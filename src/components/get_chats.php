<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLogin();

try
{
    $stmt = $pdo->prepare("
        SELECT c.id, c.chat_name, c.chat_type
        FROM chat c
        INNER JOIN chat_participant cp ON c.id = cp.chat_id
        WHERE cp.user_id = ?
        ORDER BY c.id ASC
    ");
    $stmt->execute(array($user_id));
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonSuccess(['chats' => $chats]);
}
catch (PDOException $e)
{
    jsonError('Fehler beim Laden der Chats');
}