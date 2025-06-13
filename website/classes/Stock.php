<?php
class Stock {
    private $db;

    public function __construct() {
        $this->db = MockDatabase::getInstance();
    }

    public function getRecentlyViewed(int $userId, int $limit = 10): array {
        // Return mock stocks since we're using mock data
        return array_slice($this->db->getMockStocks(), 0, $limit);
    }

    public function getPopularStocks(): array {
        $stocks = $this->db->getMockStocks();
        
        // Add some mock data structure that the dashboard expects
        foreach ($stocks as &$stock) {
            if (!isset($stock['data'])) {
                $stock['data'] = [
                    'change_percent' => $stock['change_percent'] ?? 0
                ];
            }
        }
        
        return $stocks;
    }

    public function getMarketIndices(): array {
        // Return mock market indices
        return [
            [
                'symbol' => 'SPY',
                'name' => 'SPDR S&P 500 ETF',
                'price' => 432.50,
                'change' => 2.15,
                'change_percent' => 0.50
            ],
            [
                'symbol' => 'QQQ',
                'name' => 'Invesco QQQ Trust',
                'price' => 375.80,
                'change' => -1.25,
                'change_percent' => -0.33
            ],
            [
                'symbol' => 'DIA',
                'name' => 'SPDR Dow Jones Industrial Average ETF',
                'price' => 342.60,
                'change' => 0.85,
                'change_percent' => 0.25
            ]
        ];
    }

    public function getUserFavorites(int $userId): array {
        return $this->getFavorites($userId);
    }

    public function search(string $query, int $limit = 20): array {
        return $this->searchStocks($query, $limit);
    }

    public function isInFavorites(int $userId, string $symbol): bool {
        return $this->isFavorite($userId, $symbol);
    }

    public function getHistoricalData(string $symbol, string $period = '1d'): array {
        // Convert period to days for mock data
        $days = match($period) {
            '1d' => 1,
            '5d' => 5,
            '1mo' => 30,
            '3mo' => 90,
            '6mo' => 180,
            '1y' => 365,
            '2y' => 730,
            '5y' => 1825,
            '10y' => 3650,
            'ytd' => date('z') + 1,
            'max' => 3650,
            default => 30
        };
        
        return $this->getStockHistory($symbol, $days);
    }

    public function getTickerData(string $symbol): ?array {
        $stocks = $this->db->getMockStocks();
        foreach ($stocks as $stock) {
            if ($stock['symbol'] === $symbol) {
                return $stock;
            }
        }
        return null;
    }

    public function searchStocks(string $query, int $limit = 20): array {
        $stocks = $this->db->getMockStocks();
        $results = [];
        
        $query = strtolower($query);
        
        foreach ($stocks as $stock) {
            $symbolMatch = stripos($stock['symbol'], $query) !== false;
            $nameMatch = stripos($stock['name'], $query) !== false;
            
            if ($symbolMatch || $nameMatch) {
                $results[] = [
                    'symbol' => $stock['symbol'],
                    'name' => $stock['name'],
                    'type' => 'Stock',
                    'exchange' => 'NASDAQ'
                ];
            }
        }
        
        return array_slice($results, 0, $limit);
    }

    public function addToRecentlyViewed(int $userId, string $symbol): bool {
        // Mock implementation - always returns true
        return true;
    }

    public function getFavorites(int $userId): array {
        $favorites = $this->db->getMockFavorites();
        $stocks = $this->db->getMockStocks();
        $result = [];
        
        foreach ($favorites as $favorite) {
            if ($favorite['user_id'] == $userId) {
                foreach ($stocks as $stock) {
                    if ($stock['symbol'] === $favorite['symbol']) {
                        $result[] = $stock;
                        break;
                    }
                }
            }
        }
        
        return $result;
    }

    public function addToFavorites(int $userId, string $symbol): bool {
        // Mock implementation - always returns true
        return true;
    }

    public function removeFromFavorites(int $userId, string $symbol): bool {
        // Mock implementation - always returns true
        return true;
    }

    public function isFavorite(int $userId, string $symbol): bool {
        $favorites = $this->db->getMockFavorites();
        
        foreach ($favorites as $favorite) {
            if ($favorite['user_id'] == $userId && $favorite['symbol'] === $symbol) {
                return true;
            }
        }
        
        return false;
    }

    public function getStocksBySymbols(array $symbols): array {
        $stocks = $this->db->getMockStocks();
        $result = [];
        
        foreach ($stocks as $stock) {
            if (in_array($stock['symbol'], $symbols)) {
                $result[] = $stock;
            }
        }
        
        return $result;
    }

    public function getAllStocks(int $limit = 100): array {
        return array_slice($this->db->getMockStocks(), 0, $limit);
    }

    public function updateStockData(string $symbol, array $data): bool {
        // Mock implementation - always returns true
        return true;
    }

    public function getStockHistory(string $symbol, int $days = 30): array {
        // Return mock historical data
        $basePrice = 100;
        $history = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $price = $basePrice + (rand(-500, 500) / 100);
            
            $history[] = [
                'date' => $date,
                'open' => $price,
                'high' => $price + (rand(0, 300) / 100),
                'low' => $price - (rand(0, 300) / 100),
                'close' => $price + (rand(-200, 200) / 100),
                'volume' => rand(1000000, 50000000)
            ];
        }
        
        return $history;
    }
}