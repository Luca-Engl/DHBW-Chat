<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chat_helpers.php';
require_once __DIR__ . '/user_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLogin();
$chat_id = validateChatId($_POST['chat_id'] ?? 0);
$member_input = requireNotEmpty($_POST['member_input'] ?? '', 'Bitte gib einen Benutzernamen oder E-Mail ein');

try
{
    requireGroupAccess($pdo, $chat_id, $user_id);

    $new_member = requireUserByUsernameOrEmail($pdo, $member_input);

    if (isUserChatMember($pdo, $chat_id, $new_member['id']))
    {
        jsonError($new_member['username'] . ' ist bereits in der Gruppe');
    }

    $stmt = $pdo->prepare("
        INSERT INTO chat_participant (user_id, chat_id, joined_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute(array($new_member['id'], $chat_id));

    jsonSuccess([
        'member_name' => $new_member['username']
    ], $new_member['username'] . ' wurde zur Gruppe hinzugefügt');
}
catch (PDOException $e)
{
    jsonError('Fehler beim Hinzufügen des Mitglieds');
}