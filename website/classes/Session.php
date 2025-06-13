<?php
class Session {
    
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set(string $key, $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function get(string $key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }
    
    public static function destroy(): void {
        self::start();
        session_destroy();
    }
    
    public static function logout(): void {
        self::clearUser();
        self::regenerateId();
        // Optionally destroy the entire session
        // self::destroy();
    }
    
    public static function isLoggedIn(): bool {
        return self::get('user_id') !== null;
    }
    
    public static function getUserId(): ?int {
        return self::get('user_id');
    }
    
    public static function getUser(): ?array {
        return self::get('user');
    }
    
    public static function setUser(array $user): void {
        self::set('user_id', $user['id']);
        self::set('user', $user);
    }
    
    public static function clearUser(): void {
        self::remove('user_id');
        self::remove('user');
    }
    
    public static function isAdmin(): bool {
        $user = self::getUser();
        return $user && ($user['role'] ?? '') === 'admin';
    }
    
    public static function regenerateId(): void {
        self::start();
        session_regenerate_id(true);
    }
    
    public static function setFlash(string $key, string $message): void {
        self::set('flash_' . $key, $message);
    }
    
    public static function getFlash(string $key): ?string {
        $message = self::get('flash_' . $key);
        self::remove('flash_' . $key);
        return $message;
    }
}