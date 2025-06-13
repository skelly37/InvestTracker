<?php
// classes/MockDatabase.php

class MockDatabase {
    private static $instance = null;
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function query(string $sql, array $params = []): array {
        // Mock data based on query
        if (strpos($sql, 'users') !== false) {
            return $this->getMockUsers();
        }
        
        if (strpos($sql, 'stocks') !== false || strpos($sql, 'recently_viewed') !== false) {
            return $this->getMockStocks();
        }
        
        if (strpos($sql, 'favorites') !== false) {
            return $this->getMockFavorites();
        }
        
        return [];
    }
    
    public function fetchOne(string $sql, array $params = []): ?array {
        $results = $this->query($sql, $params);
        return $results[0] ?? null;
    }
    
    public function execute(string $sql, array $params = []): bool {
        return true;
    }
    
    public function lastInsertId(): int {
        return rand(1, 1000);
    }
    
    public function getMockUsers(): array {
        return [
            [
                'id' => 1,
                'username' => 'admin',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => '2024-01-01 10:00:00',
                'last_login' => '2024-06-13 18:00:00'
            ],
            [
                'id' => 2,
                'username' => 'testuser',
                'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
                'role' => 'user',
                'is_active' => true,
                'created_at' => '2024-01-02 10:00:00',
                'last_login' => '2024-06-12 15:30:00'
            ]
        ];
    }
    
    public function getMockStocks(): array {
        return [
            [
                'symbol' => 'AAPL',
                'name' => 'Apple Inc.',
                'price' => 195.89,
                'change' => 2.45,
                'change_percent' => 1.27,
                'volume' => 45234567,
                'market_cap' => 3045000000000,
                'updated_at' => date('Y-m-d H:i:s'),
                'data' => json_encode([
                    'price' => 195.89,
                    'change' => 2.45,
                    'change_percent' => 1.27,
                    'volume' => 45234567
                ])
            ],
            [
                'symbol' => 'MSFT',
                'name' => 'Microsoft Corporation',
                'price' => 412.15,
                'change' => -5.67,
                'change_percent' => -1.36,
                'volume' => 23456789,
                'market_cap' => 3067000000000,
                'updated_at' => date('Y-m-d H:i:s'),
                'data' => json_encode([
                    'price' => 412.15,
                    'change' => -5.67,
                    'change_percent' => -1.36,
                    'volume' => 23456789
                ])
            ],
            [
                'symbol' => 'GOOGL',
                'name' => 'Alphabet Inc.',
                'price' => 175.25,
                'change' => 3.89,
                'change_percent' => 2.27,
                'volume' => 34567890,
                'market_cap' => 2145000000000,
                'updated_at' => date('Y-m-d H:i:s'),
                'data' => json_encode([
                    'price' => 175.25,
                    'change' => 3.89,
                    'change_percent' => 2.27,
                    'volume' => 34567890
                ])
            ]
        ];
    }
    
    public function getMockFavorites(): array {
        return [
            [
                'user_id' => 1,
                'symbol' => 'AAPL',
                'added_at' => '2024-06-01 10:00:00'
            ],
            [
                'user_id' => 1,
                'symbol' => 'MSFT',
                'added_at' => '2024-06-02 11:30:00'
            ]
        ];
    }
}