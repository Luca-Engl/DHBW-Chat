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
$note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;

if ($note_id <= 0)
{
    echo json_encode(['success' => false, 'message' => 'Ungültige Notiz-ID']);
    exit;
}

try
{
    $stmt = $pdo->prepare("
        SELECT n.chat_id 
        FROM note n
        WHERE n.id = ?
    ");
    $stmt->execute(array($note_id));
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$note)
    {
        echo json_encode(['success' => false, 'message' => 'Notiz nicht gefunden']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM chat_participant
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->execute(array($note['chat_id'], $user_id));
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($access['count'] == 0)
    {
        echo json_encode(['success' => false, 'message' => 'Kein Zugriff auf diesen Chat']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM note WHERE id = ?");
    $stmt->execute(array($note_id));

    echo json_encode([
        'success' => true,
        'message' => 'Notiz gelöscht'
    ]);
}
catch (PDOException $e)
{
    error_log("Delete note error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Löschen der Notiz'
    ]);
}
?>