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
            return ['success' => false, 'message' => 'Failed to add to favorites'];
        }
    }
    
    public function removeFromFavorites($userId, $symbol) {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_favorites WHERE user_id = ? AND symbol = ?");
            $stmt->execute([$userId, $symbol]);
            return ['success' => true, 'message' => 'Removed from favorites'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to remove from favorites'];
        }
    }
    
    public function isFavorite($userId, $symbol) {
        try {
            $stmt = $this->db->prepare("SELECT 1 FROM user_favorites WHERE user_id = ? AND symbol = ?");
            $stmt->execute([$userId, $symbol]);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function addToRecentlyViewed($userId, $symbol) {
        try {
            $stmt = $this->db->prepare("DELETE FROM recently_viewed WHERE user_id = ? AND symbol = ?");
            $stmt->execute([$userId, $symbol]);
            
            $stmt = $this->db->prepare("
                INSERT INTO recently_viewed (user_id, symbol, viewed_at) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$userId, $symbol]);
            
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
            return [];
        }
    }
    
    public function searchStocks($query) {
        $cacheUri = "/search?q=" . urlencode($query);
        $cachedData = $this->getCachedData($cacheUri);
        
        if ($cachedData !== null) {
            return $cachedData;
        }
        
        try {
            $url = $this->apiUrl . $cacheUri;
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
            
            $results = $data['results'];
            $this->cacheData($cacheUri, $results);

            return $results;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getQuote($symbol) {
        $cacheUri = "/quote?q=" . urlencode($symbol);
        $cachedData = $this->getCachedData($cacheUri);
        
        if ($cachedData !== null) {
            return $cachedData;
        }
        
        try {
            $url = $this->apiUrl . $cacheUri;
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
            
            $this->cacheData($cacheUri, $data);
            
            return $data;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getHistoricalData($symbol, $interval) {
        $cacheUri = "/history?q=" . urlencode($symbol) . "&interval=" . urlencode($interval);
        $cachedData = $this->getCachedData($cacheUri);
        
        if ($cachedData !== null) {
            return $cachedData;
        }
        
        try {
            $url = $this->apiUrl . $cacheUri;
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET',
                    'header' => 'User-Agent: InvestTracker/1.0'
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            if ($response === false) {
                throw new Exception("Failed to fetch history from API");
            }
            
            $data = json_decode($response, true);
            if (!$data) {
                throw new Exception("Invalid API response");
            }
            
            $this->cacheData($cacheUri, $data);

            return $data;
        } catch (Exception $e) {
            return null;
        }
    }

    private function getCachedData($uri) {
        try {
            $stmt = $this->db->prepare("
                SELECT data 
                FROM stock_cache 
                WHERE uri = ? AND created_at > NOW() - INTERVAL '5 minutes'
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$uri]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return json_decode($result['data'], true);
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function cacheData($uri, $data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO stock_cache (uri, data, created_at)
                VALUES (?, ?, NOW())
                ON CONFLICT (uri) DO UPDATE SET
                    data = EXCLUDED.data,
                    created_at = EXCLUDED.created_at
            ");
            $stmt->execute([$uri, json_encode($data)]);
        } catch (Exception $e) {
        }
    }
    
    public function getPopularStocks() {
        return ['AAPL', 'GOOGL', 'MSFT', 'TSLA'];
    }
    
    public function getMarketIndices() {
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
            return false;
        }
    }
}