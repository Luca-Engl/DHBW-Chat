<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chat_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLoginOrGuest();
$chat_id = validateChatId($_GET['chat_id'] ?? 0);

try
{
    requireChatAccess($pdo, $chat_id, $user_id);

    // Delete old messages
    $stmt = $pdo->prepare("
        DELETE FROM message
        WHERE chat_id = ?
        AND sent_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute(array($chat_id));

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
        WHERE m.chat_id = ?
        ORDER BY m.sent_at ASC
    ");
    $stmt->execute(array($chat_id));
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonSuccess([
        'messages' => $messages,
        'current_user_id' => intval($user_id)
    ]);
}
catch (PDOException $e)
{
    jsonError('Fehler beim Laden der Nachrichten');
}