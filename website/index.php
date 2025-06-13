<?php

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load environment variables if .env file exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Set default timezone
date_default_timezone_set('UTC');

// Include the router
require_once __DIR__ . '/router.php';