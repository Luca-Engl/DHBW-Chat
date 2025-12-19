<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chat_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLoginOrGuest();
$chat_id = validateChatId($_POST['chat_id'] ?? 0);
$content = requireNotEmpty($_POST['content'] ?? '', 'Notiz darf nicht leer sein');

try
{
    requireChatAccess($pdo, $chat_id, $user_id);

    $stmt = $pdo->prepare("
        INSERT INTO note (chat_id, user_id, content, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute(array($chat_id, $user_id, $content));

    $note_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        SELECT 
            n.id,
            n.content,
            n.created_at,
            u.username as author
        FROM note n
        INNER JOIN user u ON n.user_id = u.id
        WHERE n.id = ?
    ");
    $stmt->execute(array($note_id));
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    jsonSuccess(['note' => $note]);
}
catch (PDOException $e)
{
    jsonError('Fehler beim Hinzufügen der Notiz');
}