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
        SELECT u.id, u.username, u.email, cp.joined_at
        FROM user u
        INNER JOIN chat_participant cp ON u.id = cp.user_id
        WHERE cp.chat_id = ?
        ORDER BY u.username ASC
    ");
    $stmt->execute(array($chat_id));
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonSuccess(['members' => $members]);
}
catch (PDOException $e)
{
    jsonError('Fehler beim Laden der Mitglieder');
}