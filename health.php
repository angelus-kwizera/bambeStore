<?php

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/config/database.php';
    $db = getDBConnection();
    $db->query('SELECT 1');
    echo json_encode([
        'status' => 'ok',
        'app' => 'Bambe E-Commerce',
        'database' => 'connected',
        'timestamp' => date('c'),
    ]);
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode([
        'status' => 'error',
        'app' => 'Bambe E-Commerce',
        'database' => 'disconnected',
        'message' => $e->getMessage(),
    ]);
}
