<?php
class User {
    private $db;

    public function __construct() {
        $this->db = MockDatabase::getInstance();
    }

    public function authenticate(string $username, string $password): ?array {
        $users = $this->db->getMockUsers();
        
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                if (password_verify($password, $user['password_hash'])) {
                    return $user;
                }
                return null;
            }
        }
        return null;
    }

    public function login(string $username, string $password): bool {
        $user = $this->authenticate($username, $password);
        if ($user) {
            Session::setUser($user);
            return true;
        }
        return false;
    }

    public function getAll(): array {
        return $this->db->getMockUsers();
    }

    public function toggleActive(int $userId): bool {
        // Mock implementation - always returns true
        return true;
    }

    public function updateRole(int $userId, string $role): bool {
        // Mock implementation - always returns true
        return true;
    }

    public function delete(int $userId): bool {
        // Mock implementation - always returns true
        return true;
    }

    public function create(string $username, string $password, string $role = 'user'): ?int {
        // Check if user already exists
        $users = $this->db->getMockUsers();
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                return null;
            }
        }
        
        // In a real app, this would insert into database
        $newUserId = count($users) + 1;
        return $newUserId;
    }

    public function getById(int $id): ?array {
        $users = $this->db->getMockUsers();
        foreach ($users as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }

    public function register(string $username, string $password): bool {
        $userId = $this->create($username, $password);
        if ($userId) {
            $newUser = [
                'id' => $userId,
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'last_login' => null
            ];
            
            Session::setUser($newUser);
            return true;
        }
        return false;
    }

    public function getUserById(int $id): ?array {
        return $this->getById($id);
    }

    public function getAllUsers(): array {
        return $this->db->getMockUsers();
    }

    public function updateUser(int $id, array $data): bool {
        // Mock implementation - always returns true
        return true;
    }

    public function deleteUser(int $id): bool {
        // Mock implementation - always returns true
        return true;
    }

    public function changePassword(int $userId, string $newPassword): bool {
        // Mock implementation - always returns true
        return true;
    }
}