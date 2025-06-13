<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function authenticate(string $username, string $password): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, password, role, active, created_at, last_login 
                FROM users 
                WHERE username = ? AND active = true
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $this->updateLastLogin($user['id']);
                
                // Remove password from returned data
                unset($user['password']);
                return $user;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Authentication error: " . $e->getMessage());
            return null;
        }
    }
    
    public function create(string $username, string $password, string $role = 'user'): ?int {
        try {
            // Check if username already exists
            if ($this->usernameExists($username)) {
                throw new Exception("Username already exists");
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("
                INSERT INTO users (username, password, role, active, created_at) 
                VALUES (?, ?, ?, true, NOW()) 
                RETURNING id
            ");
            $stmt->execute([$username, $hashedPassword, $role]);
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("User creation error: " . $e->getMessage());
            return null;
        }
    }

    public function getById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, role, active, created_at, last_login 
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }

    public function getAll(): array {
        try {
            $stmt = $this->db->query("
                SELECT id, username, role, active, created_at, last_login 
                FROM users 
                ORDER BY created_at DESC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get all users error: " . $e->getMessage());
            return [];
        }
    }

    public function updateRole(int $userId, string $role): bool {
        try {
            $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
            return $stmt->execute([$role, $userId]);
        } catch (PDOException $e) {
            error_log("Update role error: " . $e->getMessage());
            return false;
        }
    }

    public function toggleActive(int $userId): bool {
        try {
            $stmt = $this->db->prepare("UPDATE users SET active = NOT active WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Toggle active error: " . $e->getMessage());
            return false;
        }
    }

    public function changePassword(int $userId, string $newPassword): bool {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            return $stmt->execute([$hashedPassword, $userId]);
        } catch (PDOException $e) {
            error_log("Change password error: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $userId): bool {
        try {
            $this->db->beginTransaction();
            
            // Delete user's favorites first
            $stmt = $this->db->prepare("DELETE FROM user_favorites WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Delete user
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$userId]);
            
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Delete user error: " . $e->getMessage());
            return false;
        }
    }

    private function usernameExists(string $username): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }

    private function updateLastLogin(int $userId): void {
        try {
            $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }

    public function validateUsername(string $username): array {
        $errors = [];
        
        if (empty($username)) {
            $errors[] = "Username is required";
        } elseif (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters long";
        } elseif (strlen($username) > 50) {
            $errors[] = "Username must be less than 50 characters";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = "Username can only contain letters, numbers and underscores";
        }
        
        return $errors;
    }

    public function validatePassword(string $password): array {
        $errors = [];
        
        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        } elseif (strlen($password) > 255) {
            $errors[] = "Password is too long";
        }
        
        return $errors;
    }
}