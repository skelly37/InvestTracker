<?php
class MockUser {
    private $db;
    
    public function __construct() {
        $this->db = MockDatabase::getInstance();
    }
    
    public function getAll(): array {
        return $this->db->getMockUsers();
    }
    
    public function findByUsername(string $username): ?array {
        $users = $this->db->getMockUsers();
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                return $user;
            }
        }
        return null;
    }
    
    public function findById(int $id): ?array {
        $users = $this->db->getMockUsers();
        foreach ($users as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }
    
    public function create(string $username, string $password, string $role = 'user'): ?int {
        return rand(100, 999); // Mock user ID
    }
    
    public function updateLastLogin(int $userId): bool {
        return true;
    }
    
    public function toggleActive(int $userId): bool {
        return true;
    }
    
    public function updateRole(int $userId, string $role): bool {
        return true;
    }
    
    public function delete(int $userId): bool {
        return true;
    }
    
    public function verifyPassword(string $password, string $hash): bool {
        // For mock, allow simple passwords
        return $password === 'admin123' || $password === 'test123';
    }
}