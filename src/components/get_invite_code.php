<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chat_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLogin();
$chat_id = validateChatId($_GET['chat_id'] ?? 0);

try
{
    requireChatAccess($pdo, $chat_id, $user_id, 'Kein Zugriff');

    $stmt = $pdo->prepare("
        SELECT invite_code FROM chat 
        WHERE id = ? AND chat_type = 'group'
    ");
    $stmt->execute(array($chat_id));
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat || !$chat['invite_code'])
    {
        jsonError('Kein Einladungscode verfügbar');
    }

    jsonSuccess(['invite_code' => $chat['invite_code']]);
}
catch (PDOException $e)
{
    jsonError('Datenbankfehler');
}