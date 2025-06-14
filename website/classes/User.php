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

            if (!$user) {
                return null;
            }

            if (!$user['active']) {
                throw new Exception('ACCOUNT_INACTIVE');
            }

            if (password_verify($password, $user['password'])) {
                $updateStmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);

                return $user;
            }

            return null;
        } catch (Exception $e) {
            if ($e->getMessage() === 'ACCOUNT_INACTIVE') {
                throw $e;
            }
            return null;
        }
    }
    
    public function register($username, $password, $role = 'user') {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                return 'USER_EXISTS';
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $result = $stmt->execute([$username, $hashedPassword, $role]);
            
            if ($result) {
                return $this->db->lastInsertId();
            } else {
                return false;
            }
            
        } catch (PDOException $e) {
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
            return false;
        }
    }
    
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getAllUsers() {
        try {
            $stmt = $this->db->query("SELECT id, username, role, active, created_at, last_login FROM users ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function updateRole($userId, $role) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
            return $stmt->execute([$role, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function toggleActive($userId) {
        try {
            $currentStatus = $this->isActive($userId);

            $newStatus = $currentStatus ? 0 : 1;

            $stmt = $this->db->prepare("UPDATE users SET active = ? WHERE id = ?");
            $result = $stmt->execute([$newStatus, $userId]);

            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function isActive(int $userId): bool {
        try {
            $stmt = $this->db->prepare("SELECT active FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result === false) {
                return true;
            }
            
            $isActiveValue = isset($result['active']) ? (int)$result['active'] : 1;
            $isActive = $isActiveValue === 1;

            return $isActive;
        } catch (PDOException $e) {
            return true;
        }
    }
    
    public function deleteUser($userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
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