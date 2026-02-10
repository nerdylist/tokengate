<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../classes/Database.php';

// Get database instance
$db = Database::getInstance()->getConnection();

Auth::syncAdminUser();

if (!Auth::isAdmin()) {
    require_once __DIR__ . '/../config/session.php';
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: ' . url('index'));
    exit;
}
