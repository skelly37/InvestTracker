<?php
// index.php

// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable mock mode FIRST
require_once __DIR__ . '/config/mock.php';

// Load mock classes
require_once __DIR__ . '/classes/MockDatabase.php';

// Load other classes
require_once __DIR__ . '/includes/functions.php';
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

// Initialize router
require_once __DIR__ . '/router.php';