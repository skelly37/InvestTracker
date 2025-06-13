<?php
class MockStock {
    private $db;
    
    public function __construct() {
        $this->db = MockDatabase::getInstance();
    }
    
    public function getRecentlyViewed(int $userId, int $limit = 5): array {
        $stocks = $this->db->getMockStocks();
        return array_slice($stocks, 0, $limit);
    }
    
    public function getPopularStocks(int $limit = 10): array {
        return $this->db->getMockStocks();
    }
    
    public function getMarketIndices(): array {
        return [
            [
                'symbol' => '^GSPC',
                'name' => 'S&P 500',
                'price' => 4567.89,
                'change' => 23.45,
                'change_percent' => 0.52
            ],
            [
                'symbol' => '^DJI',
                'name' => 'Dow Jones',
                'price' => 35678.90,
                'change' => -145.67,
                'change_percent' => -0.41
            ],
            [
                'symbol' => '^IXIC',
                'name' => 'NASDAQ',
                'price' => 14567.89,
                'change' => 67.89,
                'change_percent' => 0.47
            ]
        ];
    }
    
    public function getUserFavorites(int $userId): array {
        $favorites = $this->db->getMockFavorites();
        $stocks = $this->db->getMockStocks();
        
        $result = [];
        foreach ($favorites as $fav) {
            if ($fav['user_id'] == $userId) {
                foreach ($stocks as $stock) {
                    if ($stock['symbol'] == $fav['symbol']) {
                        $result[] = array_merge($stock, ['added_at' => $fav['added_at']]);
                        break;
                    }
                }
            }
        }
        
        return $result;
    }
    
    public function addToFavorites(int $userId, string $symbol): bool {
        return true; // Mock always succeeds
    }
    
    public function removeFromFavorites(int $userId, string $symbol): bool {
        return true; // Mock always succeeds
    }
    
    public function search(string $query, int $limit = 10): array {
        $stocks = $this->db->getMockStocks();
        return array_filter($stocks, function($stock) use ($query) {
            return stripos($stock['symbol'], $query) !== false || 
                   stripos($stock['name'], $query) !== false;
        });
    }
    
    public function getBySymbol(string $symbol): ?array {
        $stocks = $this->db->getMockStocks();
        foreach ($stocks as $stock) {
            if ($stock['symbol'] === strtoupper($symbol)) {
                return $stock;
            }
        }
        return null;
    }
}