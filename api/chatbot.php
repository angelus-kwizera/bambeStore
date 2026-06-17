<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/ai-chat.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$message = trim($input['message'] ?? '');
$history = $input['history'] ?? [];

if (!is_array($history)) {
    $history = [];
}

$db = getDBConnection();
$result = getChatbotResponse($db, $message, $history);

echo json_encode([
    'reply' => $result['reply'],
    'source' => $result['source'],
    'ai_enabled' => isAIConfigured(),
]);
