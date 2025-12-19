<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chat_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLoginOrGuest();
$message_id = validateMessageId($_POST['message_id'] ?? 0);
$new_content = requireNotEmpty($_POST['content'] ?? '', 'Nachricht darf nicht leer sein');

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
        jsonError('Du kannst nur deine eigenen Nachrichten bearbeiten');
    }

    requireChatAccess($pdo, $message['chat_id'], $user_id);

    $stmt = $pdo->prepare("
        UPDATE message 
        SET content = ?, edited_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute(array($new_content, $message_id));

    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.content,
            m.sent_at,
            m.edited_at,
            m.sender_id,
            u.username as sender_name
        FROM message m
        INNER JOIN user u ON m.sender_id = u.id
        WHERE m.id = ?
    ");
    $stmt->execute(array($message_id));
    $updated_message = $stmt->fetch(PDO::FETCH_ASSOC);

    jsonSuccess([
        'message' => $updated_message,
        'current_user_id' => intval($user_id)
    ]);
}
catch (PDOException $e)
{
    jsonError('Fehler beim Bearbeiten der Nachricht');
}