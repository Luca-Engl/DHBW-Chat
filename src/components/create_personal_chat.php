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
$contact_input = isset($_POST['contact_input']) ? trim($_POST['contact_input']) : '';

if (empty($contact_input))
{
    echo json_encode(['success' => false, 'message' => 'Bitte gib einen Benutzernamen oder E-Mail ein']);
    exit;
}

try
{
    // Find the other user by username or email
    if (filter_var($contact_input, FILTER_VALIDATE_EMAIL))
    {
        $stmt = $pdo->prepare("SELECT id, username FROM `user` WHERE email = ?");
        $stmt->execute(array($contact_input));
    }
    else
    {
        $stmt = $pdo->prepare("SELECT id, username FROM `user` WHERE username = ?");
        $stmt->execute(array($contact_input));
    }

    $other_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$other_user)
    {
        echo json_encode(['success' => false, 'message' => 'Benutzer nicht gefunden']);
        exit;
    }

    $other_user_id = $other_user['id'];

    // Check if user is trying to add themselves
    if ($other_user_id == $user_id)
    {
        echo json_encode(['success' => false, 'message' => 'Du kannst keinen Chat mit dir selbst erstellen']);
        exit;
    }

    // Check if a personal chat between these two users already exists
    $stmt = $pdo->prepare("
        SELECT c.id, c.chat_name
        FROM chat c
        INNER JOIN chat_participant cp1 ON c.id = cp1.chat_id
        INNER JOIN chat_participant cp2 ON c.id = cp2.chat_id
        WHERE c.chat_type = 'personal'
        AND cp1.user_id = ?
        AND cp2.user_id = ?
    ");
    $stmt->execute(array($user_id, $other_user_id));
    $existing_chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_chat)
    {
        echo json_encode([
            'success' => true,
            'message' => 'Chat existiert bereits',
            'chat_id' => intval($existing_chat['id']),
            'chat_name' => $existing_chat['chat_name'],
            'already_exists' => true
        ]);
        exit;
    }

    // Create new personal chat
    $pdo->beginTransaction();

    // Get current user's username for chat name
    $stmt = $pdo->prepare("SELECT username FROM `user` WHERE id = ?");
    $stmt->execute(array($user_id));
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create chat name as "User1 & User2"
    $chat_name = $current_user['username'] . ' & ' . $other_user['username'];

    $stmt = $pdo->prepare("
        INSERT INTO chat (chat_name, chat_type, created_at)
        VALUES (?, 'personal', NOW())
    ");
    $stmt->execute(array($chat_name));

    $chat_id = $pdo->lastInsertId();

    // Add both users to the chat
    $stmt = $pdo->prepare("
        INSERT INTO chat_participant (user_id, chat_id, joined_at)
        VALUES (?, ?, NOW())
    ");

    $stmt->execute(array($user_id, $chat_id));
    $stmt->execute(array($other_user_id, $chat_id));

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Chat mit ' . $other_user['username'] . ' erstellt',
        'chat_id' => intval($chat_id),
        'chat_name' => $chat_name,
        'already_exists' => false
    ]);
}
catch (PDOException $e)
{
    if ($pdo->inTransaction())
    {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Erstellen des Chats'
    ]);
}