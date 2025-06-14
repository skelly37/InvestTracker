<?php

// Load configuration and classes
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Session.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Stock.php';
require_once __DIR__ . '/classes/Validator.php';
require_once __DIR__ . '/controllers/BaseController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/StockController.php';
require_once __DIR__ . '/controllers/UserController.php';

// Start session
Session::start();

// Get request URI and method
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove trailing slash (except for root)
if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
    $requestUri = rtrim($requestUri, '/');
}

// Define routes
$routes = [
    'GET' => [
        '/' => ['AuthController', 'showLogin'],
        '/login' => ['AuthController', 'showLogin'],
        '/dashboard' => ['DashboardController', 'index'],
        '/favorites' => ['DashboardController', 'favorites'],
        '/settings' => ['DashboardController', 'settings'],
        '/search' => ['StockController', 'search'],
        '/stock' => ['StockController', 'detail'],
        '/stock/quote' => ['StockController', 'quote'],
        '/stock/autocomplete' => ['StockController', 'autocomplete'],
        '/users' => ['UserController', 'index'],
        '/logout' => ['AuthController', 'logout'],
    ],
    'POST' => [
        '/login' => ['AuthController', 'login'],
        '/register' => ['AuthController', 'register'],
        '/auth/change-password' => ['AuthController', 'changePassword'],
        '/dashboard/add-favorite' => ['DashboardController', 'addFavorite'],
        '/dashboard/remove-favorite' => ['DashboardController', 'removeFavorite'],
        '/dashboard/update-preferences' => ['DashboardController', 'updatePreferences'],
        '/users/create' => ['UserController', 'create'],
        '/users/update-role' => ['UserController', 'updateRole'],
        '/users/toggle-active' => ['UserController', 'toggleActive'],
        '/users/delete' => ['UserController', 'delete'],
        '/dashboard/clear-history' => ['DashboardController', 'clearRecentHistory'],
    ]
];

// Handle routing
try {
    if (isset($routes[$requestMethod][$requestUri])) {
        [$controllerName, $method] = $routes[$requestMethod][$requestUri];
        
        $controller = new $controllerName();
        $controller->$method();
    } else {
        // 404 Not Found
        http_response_code(404);
        
        if (Session::isLoggedIn()) {
            require_once __DIR__ . '/views/layouts/header.php';
            require_once __DIR__ . '/views/layouts/navigation.php';
        } else {
            require_once __DIR__ . '/views/layouts/header.php';
        }
        
        echo '<div class="container text-center mt-3">';
        echo '<h1>404 - Page Not Found</h1>';
        echo '<p>The requested page could not be found.</p>';
        if (Session::isLoggedIn()) {
            echo '<a href="/dashboard" class="btn btn--primary">Go to Dashboard</a>';
        } else {
            echo '<a href="/login" class="btn btn--primary">Go to Login</a>';
        }
        echo '</div>';
        
        require_once __DIR__ . '/views/layouts/footer.php';
    }
} catch (Exception $e) {
    // 500 Internal Server Error
    error_log("Router error: " . $e->getMessage());
    http_response_code(500);
    
    if (Session::isLoggedIn()) {
        require_once __DIR__ . '/views/layouts/header.php';
        require_once __DIR__ . '/views/layouts/navigation.php';
    } else {
        require_once __DIR__ . '/views/layouts/header.php';
    }
    
    echo '<div class="container text-center mt-3">';
    echo '<h1>500 - Internal Server Error</h1>';
    echo '<p>Something went wrong. Please try again later.</p>';
    if (Session::isLoggedIn()) {
        echo '<a href="/dashboard" class="btn btn--primary">Go to Dashboard</a>';
    } else {
        echo '<a href="/login" class="btn btn--primary">Go to Login</a>';
    }
    echo '</div>';
    
    require_once __DIR__ . '/views/layouts/footer.php';
}