<?php
header('Content-Type: application/json');
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

$user_id = $_SESSION['user_id'];

try
{
    $stmt = $pdo->prepare("
        SELECT c.id, c.chat_name, c.chat_type
        FROM chat c
        INNER JOIN chat_participant cp ON c.id = cp.chat_id
        WHERE cp.user_id = ?
        ORDER BY c.id ASC
    ");
    $stmt->execute(array($user_id));

    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'chats' => $chats
    ]);
}
catch (PDOException $e)
{
    error_log("GET CHATS ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Laden der Chats'
    ]);
}
?>