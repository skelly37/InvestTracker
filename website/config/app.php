<?php
return [
    'app_name' => 'InvestTracker',
    'app_url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'yahoo_api_url' => $_ENV['YAHOO_API_URL'] ?? 'http://yahoo_wrapper:5000',
    'session_lifetime' => 3600, // 1 hour
    'timezone' => 'UTC',
];