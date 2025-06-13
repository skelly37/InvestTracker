<?php
class Stock {
    private $db;
    private $apiBaseUrl;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $config = require_once __DIR__ . '/../config/app.php';
        $this->apiBaseUrl = $config['yahoo_api_url'];
    }
    
    public function search(string $query): array {
        try {
            $response = $this->makeApiRequest("/search", ['q' => $query]);
            
            if ($response && isset($response['results'])) {
                return $response['results'];
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Stock search error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTickerData(string $symbol): ?array {
        try {
            $response = $this->makeApiRequest("/ticker", ['symbol' => $symbol]);
            
            if ($response && isset($response['data'])) {
                // Cache the data
                $this->cacheTickerData($symbol, $response['data']);
                return $response['data'];
            }
            
            // Fallback to cached data if API fails
            return $this->getCachedTickerData($symbol);
        } catch (Exception $e) {
            error_log("Get ticker data error: " . $e->getMessage());
            return $this->getCachedTickerData($symbol);
        }
    }
    
    public function getHistoricalData(string $symbol, string $period = '1d'): array {
        try {
            $response = $this->makeApiRequest("/historical", [
                'symbol' => $symbol,
                'period' => $period
            ]);
            
            if ($response && isset($response['data'])) {
                return $response['data'];
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Get historical data error: " . $e->getMessage());
            return [];
        }
    }
    
    public function addToFavorites(int $userId, string $symbol): bool {
        try {
            // Check if already exists
            if ($this->isInFavorites($userId, $symbol)) {
                return true; // Already in favorites
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO user_favorites (user_id, symbol, created_at) 
                VALUES (?, ?, NOW())
                ON CONFLICT (user_id, symbol) DO NOTHING
            ");
            return $stmt->execute([$userId, $symbol]);
        } catch (PDOException $e) {
            error_log("Add to favorites error: " . $e->getMessage());
            return false;
        }
    }
    
    public function removeFromFavorites(int $userId, string $symbol): bool {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM user_favorites 
                WHERE user_id = ? AND symbol = ?
            ");
            return $stmt->execute([$userId, $symbol]);
        } catch (PDOException $e) {
            error_log("Remove from favorites error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserFavorites(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT uf.symbol, uf.created_at, sc.data as cached_data
                FROM user_favorites uf
                LEFT JOIN stock_cache sc ON uf.symbol = sc.symbol 
                    AND sc.created_at > NOW() - INTERVAL '15 minutes'
                WHERE uf.user_id = ?
                ORDER BY uf.created_at DESC
            ");
            $stmt->execute([$userId]);
            $favorites = $stmt->fetchAll();
            
            // Enrich with current data
            foreach ($favorites as &$favorite) {
                if (!$favorite['cached_data']) {
                    $favorite['data'] = $this->getTickerData($favorite['symbol']);
                } else {
                    $favorite['data'] = json_decode($favorite['cached_data'], true);
                }
            }
            
            return $favorites;
        } catch (PDOException $e) {
            error_log("Get user favorites error: " . $e->getMessage());
            return [];
        }
    }
    
    public function isInFavorites(int $userId, string $symbol): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM user_favorites 
                WHERE user_id = ? AND symbol = ?
            ");
            $stmt->execute([$userId, $symbol]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Check favorites error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getRecentlyViewed(int $userId, int $limit = 10): array {
        try {
            $stmt = $this->db->prepare("
                SELECT rv.symbol, rv.viewed_at, sc.data as cached_data
                FROM recently_viewed rv
                LEFT JOIN stock_cache sc ON rv.symbol = sc.symbol 
                    AND sc.created_at > NOW() - INTERVAL '15 minutes'
                WHERE rv.user_id = ?
                ORDER BY rv.viewed_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            $recent = $stmt->fetchAll();
            
            // Enrich with current data
            foreach ($recent as &$item) {
                if (!$item['cached_data']) {
                    $item['data'] = $this->getTickerData($item['symbol']);
                } else {
                    $item['data'] = json_decode($item['cached_data'], true);
                }
            }
            
            return $recent;
        } catch (PDOException $e) {
            error_log("Get recently viewed error: " . $e->getMessage());
            return [];
        }
    }
    
    public function addToRecentlyViewed(int $userId, string $symbol): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO recently_viewed (user_id, symbol, viewed_at) 
                VALUES (?, ?, NOW())
                ON CONFLICT (user_id, symbol) 
                DO UPDATE SET viewed_at = NOW()
            ");
            return $stmt->execute([$userId, $symbol]);
        } catch (PDOException $e) {
            error_log("Add to recently viewed error: " . $e->getMessage());
            return false;
        }
    }
    
    private function makeApiRequest(string $endpoint, array $params = []): ?array {
        $url = $this->apiBaseUrl . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'GET',
                'header' => [
                    'Accept: application/json',
                    'User-Agent: InvestTracker/1.0'
                ]
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception("API request failed: $url");
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response from API");
        }
        
        return $data;
    }
    
    private function cacheTickerData(string $symbol, array $data): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO stock_cache (symbol, data, created_at) 
                VALUES (?, ?, NOW())
                ON CONFLICT (symbol) 
                DO UPDATE SET data = EXCLUDED.data, created_at = NOW()
            ");
            $stmt->execute([$symbol, json_encode($data)]);
        } catch (PDOException $e) {
            error_log("Cache ticker data error: " . $e->getMessage());
        }
    }
    
    private function getCachedTickerData(string $symbol): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT data FROM stock_cache 
                WHERE symbol = ? AND created_at > NOW() - INTERVAL '15 minutes'
            ");
            $stmt->execute([$symbol]);
            $result = $stmt->fetchColumn();
            
            return $result ? json_decode($result, true) : null;
        } catch (PDOException $e) {
            error_log("Get cached ticker data error: " . $e->getMessage());
            return null;
        }
    }
    
    public function getPopularStocks(): array {
        // Return some popular stocks for dashboard
        $popular = ['AAPL', 'GOOGL', 'MSFT', 'TSLA', 'NVDA', 'META', 'AMZN', 'BRK.B'];
        $result = [];
        
        foreach ($popular as $symbol) {
            $data = $this->getTickerData($symbol);
            if ($data) {
                $result[] = [
                    'symbol' => $symbol,
                    'data' => $data
                ];
            }
        }
        
        return $result;
    }
    
    public function getMarketIndices(): array {
        $indices = ['^GSPC', '^IXIC', '^DJI', '^RUT', '^NYA'];
        $result = [];
        
        foreach ($indices as $symbol) {
            $data = $this->getTickerData($symbol);
            if ($data) {
                $result[] = [
                    'symbol' => $symbol,
                    'data' => $data
                ];
            }
        }
        
        return $result;
    }
}