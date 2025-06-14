<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

require_once __DIR__ . '/router.php';