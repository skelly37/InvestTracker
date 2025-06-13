<?php
abstract class BaseController {
    protected $user;
    protected $stock;
    
    public function __construct() {
        $this->user = new User();
        $this->stock = new Stock();
        
        // Start session
        Session::start();
        
        // Set timezone safely - require without require_once to get array
        $config = require __DIR__ . '/../config/app.php';
        $timezone = $config['timezone'] ?? 'UTC';
        date_default_timezone_set($timezone);
    }
    
    protected function requireAuth(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }
    }
    
    protected function requireAdmin(): void {
        $this->requireAuth();
        if (!Session::isAdmin()) {
            $this->redirect('/dashboard', 'Access denied. Admin privileges required.');
        }
    }
    
    protected function redirect(string $url, string $message = null): void {
        if ($message) {
            Session::set('flash_message', $message);
        }
        header("Location: $url");
        exit;
    }
    
    protected function view(string $view, array $data = []): void {
        extract($data);
        
        // Global variables for views
        $currentUser = Session::getUser();
        $isLoggedIn = Session::isLoggedIn();
        $isAdmin = Session::isAdmin();
        $flashMessage = Session::get('flash_message');
        if ($flashMessage) {
            Session::remove('flash_message');
        }
        
        // Helper functions for views
        $this->loadHelperFunctions();
        
        require_once __DIR__ . "/../views/$view.php";
    }
    
    protected function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function validateCSRF(): bool {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        return hash_equals(Session::get('csrf_token', ''), $token);
    }
    
    protected function generateCSRF(): string {
        $token = bin2hex(random_bytes(32));
        Session::set('csrf_token', $token);
        return $token;
    }
    
    protected function sanitizeInput(array $data): array {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
    
    protected function getRequestMethod(): string {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    protected function isPost(): bool {
        return $this->getRequestMethod() === 'POST';
    }
    
    protected function isGet(): bool {
        return $this->getRequestMethod() === 'GET';
    }
    
    private function loadHelperFunctions(): void {
        if (!function_exists('isCurrentPage')) {
            function isCurrentPage(string $path): bool {
                return $_SERVER['REQUEST_URI'] === $path || 
                       strpos($_SERVER['REQUEST_URI'], $path) === 0;
            }
        }
        
        if (!function_exists('isAdmin')) {
            function isAdmin(): bool {
                return Session::isAdmin();
            }
        }
        
        if (!function_exists('formatPrice')) {
            function formatPrice(?float $price): string {
                return $price ? '$' . number_format($price, 2) : 'N/A';
            }
        }
        
        if (!function_exists('formatChange')) {
            function formatChange(?float $change, ?float $changePercent): string {
                if ($change === null || $changePercent === null) {
                    return 'N/A';
                }
                
                $sign = $change >= 0 ? '+' : '';
                $class = $change > 0 ? 'text--success' : ($change < 0 ? 'text--danger' : 'text--neutral');
                
                return "<span class=\"$class\">{$sign}" . number_format($change, 2) . 
                       " ({$sign}" . number_format($changePercent, 2) . "%)</span>";
            }
        }
        
        if (!function_exists('formatDate')) {
            function formatDate(?string $date): string {
                if (!$date) return 'N/A';
                return date('M j, Y g:i A', strtotime($date));
            }
        }
        
        if (!function_exists('csrf_token')) {
            function csrf_token(): string {
                return Session::get('csrf_token', '');
            }
        }
        
        if (!function_exists('old')) {
            function old(string $key, string $default = ''): string {
                return Session::get("old_$key", $default);
            }
        }
    }
}