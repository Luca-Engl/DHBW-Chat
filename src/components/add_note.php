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
$chat_id = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if ($chat_id <= 0)
{
    echo json_encode(['success' => false, 'message' => 'Ungültige Chat-ID']);
    exit;
}

if (empty($content))
{
    echo json_encode(['success' => false, 'message' => 'Notiz darf nicht leer sein']);
    exit;
}

try
{
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

    $stmt = $pdo->prepare("
        INSERT INTO note (chat_id, user_id, content, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute(array($chat_id, $user_id, $content));

    $note_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        SELECT 
            n.id,
            n.content,
            n.created_at,
            u.username as author
        FROM note n
        INNER JOIN user u ON n.user_id = u.id
        WHERE n.id = ?
    ");
    $stmt->execute(array($note_id));
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'note' => $note
    ]);
}
catch (PDOException $e)
{
    error_log("Add note error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Hinzufügen der Notiz'
    ]);
}
?>