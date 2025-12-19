<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db_connect.php';

$code = isset($_GET['code']) ? strtoupper(trim($_GET['code'])) : '';

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Kein Code angegeben']);
    exit;
}

// Code validieren
if (!preg_match('/^[A-Z0-9]{6}$/', $code)) {
    echo json_encode(['success' => false, 'message' => 'Ungültiges Code-Format']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, chat_name 
        FROM chat 
        WHERE invite_code = ? AND chat_type = 'group'
    ");
    $stmt->execute([$code]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($chat) {
        echo json_encode([
            'success' => true,
            'chat_name' => $chat['chat_name']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ungültiger Code'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Datenbankfehler']);
}