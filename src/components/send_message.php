<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chat_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLoginOrGuest();
$chat_id = validateChatId($_POST['chat_id'] ?? 0);
$content = requireNotEmpty($_POST['content'] ?? '', 'Nachricht darf nicht leer sein');

try
{
    requireChatAccess($pdo, $chat_id, $user_id);

    $stmt = $pdo->prepare("
        INSERT INTO message (chat_id, sender_id, content, sent_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute(array($chat_id, $user_id, $content));

    $message_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.content,
            m.sent_at,
            m.sender_id,
            u.username as sender_name
        FROM message m
        INNER JOIN user u ON m.sender_id = u.id
        WHERE m.id = ?
    ");
    $stmt->execute(array($message_id));
    $message = $stmt->fetch();

    jsonSuccess([
        'message' => $message,
        'current_user_id' => $user_id
    ]);
}
catch (PDOException $e)
{
    jsonError('Fehler beim Senden der Nachricht');
}