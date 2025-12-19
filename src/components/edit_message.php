<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db_connect.php';

if (session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true)
{
    if (empty($_SESSION['isGuest']) || empty($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
        exit;
    }
}

$user_id = $_SESSION['user_id'];
$message_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
$new_content = isset($_POST['content']) ? trim($_POST['content']) : '';

if ($message_id <= 0)
{
    echo json_encode(['success' => false, 'message' => 'Ungültige Nachrichten-ID']);
    exit;
}

if (empty($new_content))
{
    echo json_encode(['success' => false, 'message' => 'Nachricht darf nicht leer sein']);
    exit;
}

try
{
    $stmt = $pdo->prepare("
        SELECT m.id, m.sender_id, m.chat_id
        FROM message m
        WHERE m.id = ?
    ");
    $stmt->execute(array($message_id));
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message)
    {
        echo json_encode(['success' => false, 'message' => 'Nachricht nicht gefunden']);
        exit;
    }

    if ($message['sender_id'] != $user_id)
    {
        echo json_encode(['success' => false, 'message' => 'Du kannst nur deine eigenen Nachrichten bearbeiten']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM chat_participant
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->execute(array($message['chat_id'], $user_id));
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($access['count'] == 0)
    {
        echo json_encode(['success' => false, 'message' => 'Kein Zugriff auf diesen Chat']);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE message 
        SET content = ?, edited_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute(array($new_content, $message_id));

    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.content,
            m.sent_at,
            m.edited_at,
            m.sender_id,
            u.username as sender_name
        FROM message m
        INNER JOIN user u ON m.sender_id = u.id
        WHERE m.id = ?
    ");
    $stmt->execute(array($message_id));
    $updated_message = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => $updated_message,
        'current_user_id' => intval($user_id)
    ]);
}
catch (PDOException $e)
{
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Bearbeiten der Nachricht'
    ]);
}