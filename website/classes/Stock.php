<?php

class Stock {
    private $db;
    private $apiUrl;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $config = require __DIR__ . '/../config/app.php';
        $this->apiUrl = $config['yahoo_api_url'];
    }
    
    public function getUserFavorites($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT symbol, created_at as added_at 
                FROM user_favorites 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get favorites error: " . $e->getMessage());
            return [];
        }
    }
    
    public function addToFavorites($userId, $symbol) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_favorites (user_id, symbol, created_at) 
                VALUES (?, ?, NOW())
                ON CONFLICT (user_id, symbol) DO NOTHING
            ");
            $stmt->execute([$userId, $symbol]);
            return ['success' => true, 'message' => 'Added to favorites'];
        } catch (Exception $e) {
            error_log("Add to favorites error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to add to favorites'];
        }
    }
    
    public function removeFromFavorites($userId, $symbol) {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_favorites WHERE user_id = ? AND symbol = ?");
            $stmt->execute([$userId, $symbol]);
            return ['success' => true, 'message' => 'Removed from favorites'];
        } catch (Exception $e) {
            error_log("Remove from favorites error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to remove from favorites'];
        }
    }
    
    public function isFavorite($userId, $symbol) {
        try {
            $stmt = $this->db->prepare("SELECT 1 FROM user_favorites WHERE user_id = ? AND symbol = ?");
            $stmt->execute([$userId, $symbol]);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            error_log("Check favorite error: " . $e->getMessage());
            return false;
        }
    }
    
    public function addToRecentlyViewed($userId, $symbol) {
        try {
            // Remove if already exists to update timestamp
            $stmt = $this->db->prepare("DELETE FROM recently_viewed WHERE user_id = ? AND symbol = ?");
            $stmt->execute([$userId, $symbol]);
            
            // Add with current timestamp
            $stmt = $this->db->prepare("
                INSERT INTO recently_viewed (user_id, symbol, viewed_at) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$userId, $symbol]);
            
            // Keep only last 50 entries per user - split into two queries for PostgreSQL compatibility
            $stmt = $this->db->prepare("
                SELECT id FROM recently_viewed 
                WHERE user_id = ? 
                ORDER BY viewed_at DESC 
                OFFSET 50
            ");
            $stmt->execute([$userId]);
            $idsToDelete = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            
            if (!empty($idsToDelete)) {
                $placeholders = str_repeat('?,', count($idsToDelete) - 1) . '?';
                $stmt = $this->db->prepare("DELETE FROM recently_viewed WHERE id IN ($placeholders)");
                $stmt->execute($idsToDelete);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Add to recently viewed error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getRecentlyViewed($userId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT symbol, viewed_at 
                FROM recently_viewed 
                WHERE user_id = ? 
                ORDER BY viewed_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get recently viewed error: " . $e->getMessage());
            return [];
        }
    }
    
    public function searchStocks($query) {
        try {
            $url = $this->apiUrl . "/search?q=" . urlencode($query);
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET',
                    'header' => 'User-Agent: InvestTracker/1.0'
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            if ($response === false) {
                throw new Exception("Failed to fetch search results");
            }
            
            $data = json_decode($response, true);
            if (!$data || !isset($data['results'])) {
                throw new Exception("Invalid search response");
            }
            
            return $data['results'];
        } catch (Exception $e) {
            error_log("Search error for '{$query}': " . $e->getMessage());
            return [];
        }
    }

    public function getQuote($symbol) {
        try {
            $url = $this->apiUrl . "/quote?q=" . urlencode($symbol);
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET',
                    'header' => 'User-Agent: InvestTracker/1.0'
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            if ($response === false) {
                throw new Exception("Failed to fetch quote from API");
            }
            
            $data = json_decode($response, true);
            if (!$data) {
                throw new Exception("Invalid API response");
            }
            
            return $data;
        } catch (Exception $e) {
            error_log("Get quote error for {$symbol}: " . $e->getMessage());
            return null;
        }
    }

    private function getCachedData($symbol) {
        try {
            $stmt = $this->db->prepare("
                SELECT data 
                FROM stock_cache 
                WHERE symbol = ? AND created_at > NOW() - INTERVAL '5 minutes'
            ");
            $stmt->execute([$symbol]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return json_decode($result['data'], true);
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Cache read error: " . $e->getMessage());
            return null;
        }
    }
    
    private function cacheData($symbol, $data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO stock_cache (symbol, data, created_at) 
                VALUES (?, ?, NOW())
                ON CONFLICT (symbol) DO UPDATE SET 
                    data = EXCLUDED.data,
                    created_at = EXCLUDED.created_at
            ");
            $stmt->execute([$symbol, json_encode($data)]);
        } catch (Exception $e) {
            error_log("Cache write error: " . $e->getMessage());
        }
    }
    
    public function getPopularStocks() {
        // Return some default popular stocks
        return [
            'AAPL', 'GOOGL', 'MSFT', 'AMZN', 'TSLA', 
            'META', 'NVDA', 'NFLX', 'ORCL', 'AMD'
        ];
    }
    
    public function getMarketIndices() {
        // Return major market indices
        return [
            '^GSPC', // S&P 500
            '^DJI',  // Dow Jones
            '^IXIC', // NASDAQ
            '^RUT'   // Russell 2000
        ];
    }

    public function clearRecentHistory(int $userId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM recently_viewed WHERE user_id = ?");
            $stmt->execute([$userId]);
            return true;
        } catch (PDOException $e) {
            error_log("Error clearing recent history: " . $e->getMessage());
            return false;
        }
    }

    public function getQuoteFromAPI(string $symbol): ?array {
        try {
            $config = require __DIR__ . '/../config/app.php';
            $apiUrl = $config['yahoo_api_url'];

            $url = $apiUrl . '/quote?q=' . urlencode($symbol);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                return json_decode($response, true);
            }

            return null;
        } catch (Exception $e) {
            error_log("API quote error: " . $e->getMessage());
            return null;
        }
    }
}