<?php
require_once __DIR__ . '/db_connect.php';

/** @var PDO $pdo */

if (session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true)
{
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

$user_id = $_SESSION['user_id'];

try
{
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.id, c.chat_name, c.chat_type, c.created_at
        FROM chat c
        INNER JOIN chat_participant cp ON c.id = cp.chat_id
        WHERE cp.user_id = ?
        ORDER BY c.created_at DESC
    ");

    $stmt->execute(array($user_id));
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($chats as &$chat)
    {
        $chat['id'] = intval($chat['id']);
    }

    echo json_encode([
        'success' => true,
        'chats' => $chats
    ]);
}
catch (PDOException $e)
{
    error_log("Get chats error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Laden der Chats'
    ]);
}
?>