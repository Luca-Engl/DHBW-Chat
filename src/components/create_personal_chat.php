<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/user_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLogin();
$contact_input = requireNotEmpty($_POST['contact_input'] ?? '', 'Bitte gib einen Benutzernamen oder E-Mail ein');

try
{
    $other_user = requireUserByUsernameOrEmail($pdo, $contact_input);
    $other_user_id = $other_user['id'];

    if ($other_user_id == $user_id)
    {
        jsonError('Du kannst keinen Chat mit dir selbst erstellen');
    }

    // Check if personal chat already exists
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
        jsonSuccess([
            'chat_id' => intval($existing_chat['id']),
            'chat_name' => $existing_chat['chat_name'],
            'already_exists' => true
        ], 'Chat existiert bereits');
    }

    $pdo->beginTransaction();

    $current_user = getUserById($pdo, $user_id, ['username']);
    $chat_name = $current_user['username'] . ' & ' . $other_user['username'];

    $stmt = $pdo->prepare("
        INSERT INTO chat (chat_name, chat_type, created_at)
        VALUES (?, 'personal', NOW())
    ");
    $stmt->execute(array($chat_name));

    $chat_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO chat_participant (user_id, chat_id, joined_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute(array($user_id, $chat_id));
    $stmt->execute(array($other_user_id, $chat_id));

    $pdo->commit();

    jsonSuccess([
        'chat_id' => intval($chat_id),
        'chat_name' => $chat_name,
        'already_exists' => false
    ], 'Chat mit ' . $other_user['username'] . ' erstellt');
}
catch (PDOException $e)
{
    if ($pdo->inTransaction())
    {
        $pdo->rollBack();
    }
    jsonError('Fehler beim Erstellen des Chats');
}