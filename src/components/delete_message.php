<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chat_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLoginOrGuest();
$message_id = validateMessageId($_POST['message_id'] ?? 0);

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
        jsonError('Nachricht nicht gefunden');
    }

    if ($message['sender_id'] != $user_id)
    {
        jsonError('Du kannst nur deine eigenen Nachrichten löschen');
    }

    requireChatAccess($pdo, $message['chat_id'], $user_id);

    $stmt = $pdo->prepare("DELETE FROM message WHERE id = ?");
    $stmt->execute(array($message_id));

    jsonSuccess([], 'Nachricht gelöscht');
}
catch (PDOException $e)
{
    jsonError('Fehler beim Löschen der Nachricht');
}