<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

try {
    // GET requests - list all ranks
    if ($method === 'GET') {
        // Fetch all ranks ordered by type and level
        $sql = "SELECT id, name, type, level, description
                FROM ranks
                ORDER BY type, level ASC";

        $ranks = $db->query($sql);

        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $ranks]);
        exit;
    }

    // Method not allowed
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
