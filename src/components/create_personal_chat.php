<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$contact_input = isset($_POST['contact_input']) ? trim($_POST['contact_input']) : '';

error_log("CREATE CHAT - User ID: " . $user_id);
error_log("CREATE CHAT - Contact Input: " . $contact_input);

if (empty($contact_input))
{
    echo json_encode(['success' => false, 'message' => 'Bitte gib einen Benutzernamen oder E-Mail ein']);
    exit;
}

try
{
    // Benutzer suchen
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

    $contact_user = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("CREATE CHAT - Contact User gefunden: " . ($contact_user ? $contact_user['username'] : 'NEIN'));

    if (!$contact_user)
    {
        echo json_encode(['success' => false, 'message' => 'Benutzer nicht gefunden']);
        exit;
    }

    if ($contact_user['id'] == $user_id)
    {
        echo json_encode(['success' => false, 'message' => 'Du kannst keinen Chat mit dir selbst erstellen']);
        exit;
    }

    // Prüfen ob Chat bereits existiert
    $stmt = $pdo->prepare("
        SELECT c.id, c.chat_name 
        FROM chat c
        WHERE c.chat_type = 'personal'
        AND c.id IN (
            SELECT chat_id 
            FROM chat_participant 
            WHERE user_id = ?
        )
        AND c.id IN (
            SELECT chat_id 
            FROM chat_participant 
            WHERE user_id = ?
        )
        AND (
            SELECT COUNT(*) 
            FROM chat_participant 
            WHERE chat_id = c.id
        ) = 2
        LIMIT 1
    ");
    $stmt->execute(array($user_id, $contact_user['id']));
    $existing_chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_chat)
    {
        error_log("CREATE CHAT - Chat existiert bereits: " . $existing_chat['id']);
        echo json_encode([
            'success' => true,
            'message' => 'Chat existiert bereits',
            'chat_id' => intval($existing_chat['id']),
            'chat_name' => $contact_user['username']
        ]);
        exit;
    }

    // Neuen Chat erstellen
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO chat (chat_name, chat_type, created_at)
        VALUES (?, 'personal', NOW())
    ");
    $stmt->execute(array($contact_user['username']));

    $chat_id = $pdo->lastInsertId();
    error_log("CREATE CHAT - Neuer Chat erstellt: " . $chat_id);

    // Beide Teilnehmer hinzufügen
    $stmt = $pdo->prepare("
        INSERT INTO chat_participant (user_id, chat_id, joined_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute(array($user_id, $chat_id));

    $stmt = $pdo->prepare("
        INSERT INTO chat_participant (user_id, chat_id, joined_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute(array($contact_user['id'], $chat_id));

    $pdo->commit();

    error_log("CREATE CHAT - Erfolgreich! Chat ID: " . $chat_id);

    echo json_encode([
        'success' => true,
        'message' => 'Chat erfolgreich erstellt',
        'chat_id' => intval($chat_id),
        'chat_name' => $contact_user['username']
    ]);
}
catch (PDOException $e)
{
    if ($pdo->inTransaction())
    {
        $pdo->rollBack();
    }
    error_log("CREATE CHAT ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Erstellen des Chats: ' . $e->getMessage()
    ]);
}
?>