<?php

define('APP_START_TIME', microtime(true));
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', __DIR__);

if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('DEBUG_MODE', true);
}

if (file_exists(APP_ROOT . '/.env')) {
    $lines = file(APP_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            
            if (!isset($_SERVER[trim($key)])) {
                $_SERVER[trim($key)] = trim($value);
            }
        }
    }
}

$timezone = $_ENV['TIMEZONE'] ?? 'UTC';
date_default_timezone_set($timezone);

require_once APP_ROOT . '/includes/functions.php';

set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $errorType = 'Unknown Error';
    switch ($severity) {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
            $errorType = 'Fatal Error';
            break;
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
            $errorType = 'Warning';
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $errorType = 'Notice';
            break;
    }
    
    $logMessage = "[$errorType] $message in $file on line $line";
    error_log($logMessage);
    
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0; color: #c62828;'>";
        echo "<strong>$errorType:</strong> $message<br>";
        echo "<small>File: $file, Line: $line</small>";
        echo "</div>";
    }
    
    return true;
});

set_exception_handler(function($exception) {
    $message = $exception->getMessage();
    $file = $exception->getFile();
    $line = $exception->getLine();
    
    error_log("Uncaught Exception: $message in $file on line $line");
    
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0; color: #c62828;'>";
        echo "<h3>Uncaught Exception</h3>";
        echo "<p><strong>Message:</strong> $message</p>";
        echo "<p><strong>File:</strong> $file</p>";
        echo "<p><strong>Line:</strong> $line</p>";
        echo "<h4>Stack Trace:</h4>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        http_response_code(500);
        echo "<h1>500 - Internal Server Error</h1>";
        echo "<p>Something went wrong. Please try again later.</p>";
    }
});

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $requestUri)) {
    $filePath = PUBLIC_ROOT . $requestUri;
    
    if (file_exists($filePath)) {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        
        $maxAge = 86400; // 1 day
        header('Cache-Control: public, max-age=' . $maxAge);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
        
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        exit('File not found');
    }
}

if (strpos($requestUri, '/api/') === 0 || strpos($requestUri, '/stock/') === 0) {
    $clientIp = get_client_ip();
    $rateLimitKey = 'api_' . $clientIp;
    
    if (!check_rate_limit($rateLimitKey, 100, 3600)) {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Rate limit exceeded',
            'message' => 'Too many requests. Please try again later.'
        ]);
        exit;
    }
}

if (defined('DEBUG_MODE') && DEBUG_MODE) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'ip' => get_client_ip(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];
    
    debug_log($logData, 'REQUEST');
}

try {
    require_once APP_ROOT . '/router.php';
} catch (Exception $e) {
    error_log('Router failed to load: ' . $e->getMessage());
    
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        throw $e;
    } else {
        http_response_code(500);
        echo "<h1>500 - Service Unavailable</h1>";
        echo "<p>The service is temporarily unavailable. Please try again later.</p>";
    }
}

if (defined('DEBUG_MODE') && DEBUG_MODE) {
    $executionTime = microtime(true) - APP_START_TIME;
    $memoryUsage = memory_get_peak_usage(true);
    
    debug_log([
        'execution_time' => number_format($executionTime * 1000, 2) . 'ms',
        'memory_usage' => format_large_number($memoryUsage) . 'B',
        'included_files' => count(get_included_files())
    ], 'PERFORMANCE');
}