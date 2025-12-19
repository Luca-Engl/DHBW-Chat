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

    $stmt = $pdo->prepare("
        SELECT 
            n.id,
            n.content,
            n.created_at,
            u.username as author
        FROM note n
        INNER JOIN user u ON n.user_id = u.id
        WHERE n.chat_id = ?
        ORDER BY n.created_at ASC
    ");
    $stmt->execute(array($chat_id));
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonSuccess(['notes' => $notes]);
}
catch (PDOException $e)
{
    jsonError('Fehler beim Laden der Notizen');
}