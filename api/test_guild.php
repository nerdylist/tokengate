<?php
// Simple test to check what's happening
error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../config/session.php';
    require_once __DIR__ . '/../classes/Auth.php';
    require_once __DIR__ . '/../classes/Database.php';

    $db = Database::getInstance();

    echo json_encode([
        'success' => true,
        'message' => 'Test successful',
        'auth_check' => Auth::check(),
        'auth_id' => Auth::check() ? Auth::id() : null
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
