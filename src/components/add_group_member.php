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
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

$user_id = $_SESSION['user_id'];
$chat_id = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;
$member_input = isset($_POST['member_input']) ? trim($_POST['member_input']) : '';

if ($chat_id <= 0)
{
    echo json_encode(['success' => false, 'message' => 'Ungültige Chat-ID']);
    exit;
}

if (empty($member_input))
{
    echo json_encode(['success' => false, 'message' => 'Bitte gib einen Benutzernamen oder E-Mail ein']);
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
        echo json_encode(['success' => false, 'message' => 'Du bist nicht Mitglied dieser Gruppe']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT chat_type, chat_name
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

    if (filter_var($member_input, FILTER_VALIDATE_EMAIL))
    {
        $stmt = $pdo->prepare("SELECT id, username FROM `user` WHERE email = ?");
        $stmt->execute(array($member_input));
    }
    else
    {
        $stmt = $pdo->prepare("SELECT id, username FROM `user` WHERE username = ?");
        $stmt->execute(array($member_input));
    }

    $new_member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$new_member)
    {
        echo json_encode(['success' => false, 'message' => 'Benutzer nicht gefunden']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM chat_participant
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->execute(array($chat_id, $new_member['id']));
    $already_member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($already_member['count'] > 0)
    {
        echo json_encode(['success' => false, 'message' => $new_member['username'] . ' ist bereits in der Gruppe']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO chat_participant (user_id, chat_id, joined_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute(array($new_member['id'], $chat_id));

    echo json_encode([
        'success' => true,
        'message' => $new_member['username'] . ' wurde zur Gruppe hinzugefügt',
        'member_name' => $new_member['username']
    ]);
}
catch (PDOException $e)
{
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Hinzufügen des Mitglieds'
    ]);
}
