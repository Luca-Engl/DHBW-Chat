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
$chat_id = isset($_GET['chat_id']) ? intval($_GET['chat_id']) : 0;

if ($chat_id <= 0)
{
    echo json_encode(['success' => false, 'message' => 'Ungültige Chat-ID']);
    exit;
}

try
{
    // Prüfen ob Benutzer Zugriff auf diesen Chat hat
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM chat_participant
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->execute(array($chat_id, $user_id));
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($access['count'] == 0)
    {
        echo json_encode(['success' => false, 'message' => 'Kein Zugriff auf diesen Chat']);
        exit;
    }

    // Nachrichten löschen, die älter als 7 Tage sind
    $stmt = $pdo->prepare("
        DELETE FROM message
        WHERE chat_id = ?
        AND sent_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute(array($chat_id));

    // Nachrichten laden
    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.content,
            m.sent_at,
            m.sender_id,
            u.username as sender_name
        FROM message m
        INNER JOIN user u ON m.sender_id = u.id
        WHERE m.chat_id = ?
        ORDER BY m.sent_at ASC
    ");

    $stmt->execute(array($chat_id));
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'current_user_id' => intval($user_id)
    ]);
}
catch (PDOException $e)
{
    error_log("Get messages error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Laden der Nachrichten: ' . $e->getMessage()
    ]);
}
?>