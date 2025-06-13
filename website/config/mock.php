<?php
define('MOCK_MODE', true);

// Mock database config to prevent connection attempts
$GLOBALS['mock_db_config'] = [
    'host' => 'localhost',
    'port' => '5432',
    'database' => 'mock_db',
    'username' => 'mock_user',
    'password' => 'mock_pass',
];

// Simple function to check if we're in mock mode
function isMockMode(): bool {
    return defined('MOCK_MODE') && MOCK_MODE === true;
}