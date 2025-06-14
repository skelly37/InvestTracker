<?php

define('APP_START_TIME', microtime(true));
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', __DIR__);

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);


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

    return true;
});

set_exception_handler(function($exception) {
    http_response_code(500);
    echo "<h1>500 - Internal Server Error</h1>";
    echo "<p>Something went wrong. Please try again later.</p>";
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


try {
    require_once APP_ROOT . '/router.php';
} catch (Exception $e) {
    http_response_code(500);
    echo "<h1>500 - Service Unavailable</h1>";
    echo "<p>The service is temporarily unavailable. Please try again later.</p>";
}
