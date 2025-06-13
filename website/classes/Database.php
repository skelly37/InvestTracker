<?php
// Override Database class to use mock in mock mode

if (function_exists('isMockMode') && isMockMode()) {
    
    class Database {
        private static $instance = null;
        private $mockDb;
        
        private function __construct() {
            $this->mockDb = MockDatabase::getInstance();
        }
        
        public static function getInstance(): self {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        public function getConnection() {
            return $this; // Return self as mock connection
        }
        
        public function query(string $sql, array $params = []): array {
            return $this->mockDb->query($sql, $params);
        }
        
        public function fetchOne(string $sql, array $params = []): ?array {
            return $this->mockDb->fetchOne($sql, $params);
        }
        
        public function execute(string $sql, array $params = []): bool {
            return $this->mockDb->execute($sql, $params);
        }
        
        public function lastInsertId(): int {
            return $this->mockDb->lastInsertId();
        }
        
        // Mock PDO methods that might be called
        public function prepare(string $sql) {
            return new MockPDOStatement($this->mockDb, $sql);
        }
    }
    
    // Mock PDOStatement for compatibility
    class MockPDOStatement {
        private $mockDb;
        private $sql;
        private $params = [];
        
        public function __construct($mockDb, $sql) {
            $this->mockDb = $mockDb;
            $this->sql = $sql;
        }
        
        public function execute($params = []) {
            $this->params = $params;
            return true;
        }
        
        public function fetchAll($fetchMode = null) {
            return $this->mockDb->query($this->sql, $this->params);
        }
        
        public function fetch($fetchMode = null) {
            return $this->mockDb->fetchOne($this->sql, $this->params);
        }
        
        public function rowCount() {
            return count($this->mockDb->query($this->sql, $this->params));
        }
    }
    
} else {
    // Original Database implementation would go here
    class Database {
        // Original implementation...
    }
}