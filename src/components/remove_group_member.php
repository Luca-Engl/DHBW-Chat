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
$chat_id = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;
$member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;

if ($chat_id <= 0 || $member_id <= 0)
{
    echo json_encode(['success' => false, 'message' => 'Ungültige Parameter']);
    exit;
}

try
{
    // Prüfen ob der User in der Gruppe ist
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM chat_participant
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->execute(array($chat_id, $user_id));
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($access['count'] == 0)
    {
        echo json_encode(['success' => false, 'message' => 'Du bist nicht Mitglied dieser Gruppe']);
        exit;
    }

    // Prüfen ob es eine Gruppe ist
    $stmt = $pdo->prepare("
        SELECT chat_type
        FROM chat
        WHERE id = ?
    ");
    $stmt->execute(array($chat_id));
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat)
    {
        echo json_encode(['success' => false, 'message' => 'Chat nicht gefunden']);
        exit;
    }

    if ($chat['chat_type'] !== 'group')
    {
        echo json_encode(['success' => false, 'message' => 'Dies ist keine Gruppe']);
        exit;
    }

    // Prüfen ob das zu entfernende Mitglied in der Gruppe ist
    $stmt = $pdo->prepare("
        SELECT u.username
        FROM user u
        INNER JOIN chat_participant cp ON u.id = cp.user_id
        WHERE cp.chat_id = ? AND cp.user_id = ?
    ");
    $stmt->execute(array($chat_id, $member_id));
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member)
    {
        echo json_encode(['success' => false, 'message' => 'Mitglied nicht in der Gruppe gefunden']);
        exit;
    }

    // Anzahl der Mitglieder prüfen
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM chat_participant
        WHERE chat_id = ?
    ");
    $stmt->execute(array($chat_id));
    $count = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($count['count'] <= 2)
    {
        echo json_encode(['success' => false, 'message' => 'Eine Gruppe muss mindestens 2 Mitglieder haben']);
        exit;
    }

    // Mitglied aus der Gruppe entfernen
    $stmt = $pdo->prepare("
        DELETE FROM chat_participant
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->execute(array($chat_id, $member_id));

    echo json_encode([
        'success' => true,
        'message' => $member['username'] . ' wurde aus der Gruppe entfernt'
    ]);
}
catch (PDOException $e)
{
    error_log("REMOVE GROUP MEMBER ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Entfernen des Mitglieds'
    ]);
}
?>