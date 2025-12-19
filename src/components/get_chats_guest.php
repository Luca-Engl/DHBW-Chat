<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/json_response.php';

$guest = getGuestAccess();

if (!$guest)
{
    jsonError('Kein Gast-Zugang');
}

$chat_id = $guest['chat_id'];

try
{
    $stmt = $pdo->prepare("
        SELECT id, chat_name, chat_type
        FROM chat
        WHERE id = ? AND chat_type = 'group'
    ");
    $stmt->execute(array($chat_id));
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat)
    {
        jsonError('Chat nicht gefunden');
    }

    jsonSuccess(['chats' => [$chat]]);
}
catch (PDOException $e)
{
    jsonError('Fehler beim Laden des Chats');
}