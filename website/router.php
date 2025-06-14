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
    '/' => ['HomeController', 'index'],
    '/login' => ['AuthController', 'login'],
    '/logout' => ['AuthController', 'logout'],
    '/register' => ['AuthController', 'register'],
    '/dashboard' => ['DashboardController', 'index'],
    '/dashboard/favorites' => ['DashboardController', 'favorites'],
    '/dashboard/add-favorite' => ['DashboardController', 'addFavorite'],
    '/dashboard/remove-favorite' => ['DashboardController', 'removeFavorite'],
    '/dashboard/settings' => ['DashboardController', 'settings'],
    '/dashboard/update-preferences' => ['DashboardController', 'updatePreferences'],
    '/dashboard/clear-recent-history' => ['DashboardController', 'clearRecentHistory'],
    '/search' => ['StockController', 'search'],
    '/stock' => ['StockController', 'detail'],
    '/stock/quote' => ['StockController', 'quote'],
    '/stock/history' => ['StockController', 'getHistoricalData'],
    '/stock/autocomplete' => ['StockController', 'autocomplete'],
    '/stock/historical' => ['StockController', 'getHistoricalData'],
    '/admin' => ['AdminController', 'index'],
    '/admin/users' => ['AdminController', 'users'],
    '/admin/users/create' => ['AdminController', 'createUser'],
    '/admin/users/edit' => ['AdminController', 'editUser'],
    '/admin/users/update' => ['AdminController', 'updateUser'],
    '/admin/users/delete' => ['AdminController', 'deleteUser'],
    '/admin/users/toggle-status' => ['AdminController', 'toggleUserStatus'],
];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (isset($routes[$uri])) {
    [$controllerName, $method] = $routes[$uri];
    
    $controllerFile = __DIR__ . "/controllers/{$controllerName}.php";
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                header("HTTP/1.0 404 Not Found");
                echo "Method not found";
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "Controller not found";
        }
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "Controller file not found";
    }
} else {
    header("HTTP/1.0 404 Not Found");
    echo "Page not found";
}
?>