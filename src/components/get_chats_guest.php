<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db_connect.php';

if (session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

if (empty($_SESSION['isGuest']) || empty($_SESSION['guest_chat_id']))
{
    echo json_encode(['success' => false, 'message' => 'Kein Gast-Zugang']);
    exit;
}

$chat_id = $_SESSION['guest_chat_id'];

try
{
    $stmt = $pdo->prepare("
        SELECT id, chat_name, chat_type
        FROM chat
        WHERE id = ?  AND chat_type = 'group'
    ");
    $stmt->execute(array($chat_id));

    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat)
    {
        echo json_encode(['success' => false, 'message' => 'Chat nicht gefunden']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'chats' => [$chat]
    ]);
}
catch (PDOException $e)
{
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Laden des Chats'
    ]);
}