<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login(string $username, string $password): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $updateStmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);

                return $user;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return null;
        }
    }
    
    public function register($username, $password, $role = 'user') {
        try {
            // Sprawdź czy username już istnieje
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                return false;
            }
            
            // Utwórz nowego użytkownika z domyślnym statusem aktywny
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (username, password, role, active, created_at) VALUES (?, ?, ?, 1, NOW())");
            $result = $stmt->execute([$username, $hashedPassword, $role]);
            
            return $result ? $this->db->lastInsertId() : false;
            
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }
    
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return false;
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            return $updateStmt->execute([$hashedPassword, $userId]);
            
        } catch (PDOException $e) {
            error_log("Change password error: " . $e->getMessage());
            return false;
        }
    }
    
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllUsers() {
        try {
            $stmt = $this->db->query("SELECT id, username, role, active, created_at, last_login FROM users ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all users error: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateRole($userId, $role) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
            return $stmt->execute([$role, $userId]);
        } catch (PDOException $e) {
            error_log("Update role error: " . $e->getMessage());
            return false;
        }
    }
    
    public function toggleActive($userId) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET active = NOT COALESCE(active, true) WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Toggle active error: " . $e->getMessage());
            return false;
        }
    }

    public function isActive(int $userId): bool {
        try {
            // Poprawka: używamy $this->db zamiast $this->pdo
            $stmt = $this->db->prepare("SELECT active FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            // Domyślnie true jeśli brak rekordu lub null
            return (bool)($result['is_active'] ?? 1);
        } catch (PDOException $e) {
            error_log("Get user active status error: " . $e->getMessage());
            return true;
        }
    }
    
    public function deleteUser($userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Delete user error: " . $e->getMessage());
            return false;
        }
    }

    public function updateChartTimeInterval($userId, $interval) {
        $validIntervals = ['1d', '5d', '1mo', '3mo', '1y', '5y', 'max'];

        if (!in_array($interval, $validIntervals)) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE users
            SET chart_time_interval = ?
            WHERE id = ?
        ");

        return $stmt->execute([$interval, $userId]);
    }

    public function getChartTimeInterval($userId) {
        $stmt = $this->db->prepare("
            SELECT chart_time_interval
            FROM users
            WHERE id = ?
        ");

        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['chart_time_interval'] : '1mo';
    }
}