<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chat_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLogin();
$chat_id = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;
$chat_name = requireNotEmpty($_POST['chat_name'] ?? '', 'Bitte gib einen Gruppennamen ein');

if ($user_id <= 0 || $chat_id <= 0)
{
    jsonError('Ungültige Parameter');
}

if (mb_strlen($chat_name, 'UTF-8') > 15)
{
    jsonError('Gruppenname darf maximal 15 Zeichen haben');
}

try
{
    requireGroupAccess($pdo, $chat_id, $user_id);

    $stmt = $pdo->prepare("UPDATE chat SET chat_name = ? WHERE id = ?");
    $stmt->execute(array($chat_name, $chat_id));

    jsonSuccess([
        'chat_id' => $chat_id,
        'chat_name' => $chat_name
    ], 'Gruppenname aktualisiert');
}
catch (PDOException $e)
{
    jsonError('Fehler beim Aktualisieren des Gruppennamens');
}