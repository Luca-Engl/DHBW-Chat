<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/json_response.php';

$code = isset($_GET['code']) ? strtoupper(trim($_GET['code'])) : '';

if (empty($code))
{
    jsonError('Kein Code angegeben');
}

if (!preg_match('/^[A-Z0-9]{6}$/', $code))
{
    jsonError('Ungültiges Code-Format');
}

try
{
    $stmt = $pdo->prepare("
        SELECT id, chat_name 
        FROM chat 
        WHERE invite_code = ? AND chat_type = 'group'
    ");
    $stmt->execute([$code]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($chat)
    {
        jsonSuccess(['chat_name' => $chat['chat_name']]);
    }
    else
    {
        jsonError('Ungültiger Code');
    }
}
catch (PDOException $e)
{
    jsonError('Datenbankfehler');
}