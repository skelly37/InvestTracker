<?php
abstract class BaseController {
    protected $user;
    protected $stock;
    
    public function __construct() {
        $this->user = new User();
        $this->stock = new Stock();
        
        Session::start();
        
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
        
        $currentUser = Session::getUser();
        $isLoggedIn = Session::isLoggedIn();
        $isAdmin = Session::isAdmin();
        $flashMessage = Session::get('flash_message');
        if ($flashMessage) {
            Session::remove('flash_message');
        }

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
}