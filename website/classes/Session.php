<?php
class Session {
    private static $started = false;

    public static function start(): void {
        if (!self::$started) {
            session_start();
            self::$started = true;
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

    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void {
        self::start();
        session_destroy();
        session_unset();
        self::$started = false;
    }

    public static function regenerateId(): void {
        self::start();
        session_regenerate_id(true);
    }

    public static function isLoggedIn(): bool {
        return self::has('user_id');
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
        self::regenerateId();
    }

    public static function logout(): void {
        self::destroy();
    }

    public static function isAdmin(): bool {
        $user = self::getUser();
        return $user && $user['role'] === 'admin';
    }

    public static function checkPermission(string $permission): bool {
        if (!self::isLoggedIn()) {
            return false;
        }

        $user = self::getUser();
        
        switch ($permission) {
            case 'manage_users':
                return $user['role'] === 'admin';
            case 'view_favorites':
            case 'manage_favorites':
                return true; // All logged in users
            default:
                return false;
        }
    }
}