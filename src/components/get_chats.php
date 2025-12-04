<?php
require_once __DIR__ . '/db_connect.php';

/** @var PDO $pdo */

if (session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true)
{
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

try
{
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            c.id,
            c.chat_name,
            c.chat_type,
            (SELECT COUNT(*) FROM message m WHERE m.chat_id = c.id) as message_count,
            (SELECT m2.sent_at FROM message m2 WHERE m2.chat_id = c.id ORDER BY m2.sent_at DESC LIMIT 1) as last_message_time
        FROM chat c
        INNER JOIN chat_participant cp ON c.id = cp.chat_id
        WHERE cp.user_id = ?
        ORDER BY last_message_time DESC, c.id ASC
    ");

    $stmt->execute(array($user_id));
    $chats = $stmt->fetchAll();

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