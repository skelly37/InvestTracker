<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Session.php';
require_once __DIR__ . '/classes/Validator.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Stock.php';
require_once __DIR__ . '/controllers/BaseController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/StockController.php';

require_once __DIR__ . '/router.php';


function loadGlobalHelperFunctions(): void {
    if (!function_exists('isCurrentPage')) {
        function isCurrentPage(string $path): bool {
            return $_SERVER['REQUEST_URI'] === $path ||
                   strpos($_SERVER['REQUEST_URI'], $path) === 0;
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
}