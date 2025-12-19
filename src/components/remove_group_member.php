<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chat_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLogin();
$chat_id = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;
$member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;

if ($user_id <= 0 || $chat_id <= 0 || $member_id <= 0)
{
    jsonError('Ungültige Parameter');
}

try
{
    $pdo->beginTransaction();

    requireGroupAccess($pdo, $chat_id, $user_id);

    // Check if target member is in group
    $stmt = $pdo->prepare("
        SELECT u.username
        FROM user u
        INNER JOIN chat_participant cp ON u.id = cp.user_id
        WHERE cp.chat_id = ? AND cp.user_id = ?
        LIMIT 1
    ");
    $stmt->execute(array($chat_id, $member_id));
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member)
    {
        $pdo->rollBack();
        jsonError('Mitglied nicht in der Gruppe gefunden');
    }

    $stmt = $pdo->prepare("
        DELETE FROM chat_participant
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->execute(array($chat_id, $member_id));

    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS count
        FROM chat_participant
        WHERE chat_id = ?
    ");
    $stmt->execute(array($chat_id));
    $remaining = $stmt->fetch(PDO::FETCH_ASSOC);
    $remaining_count = intval($remaining['count'] ?? 0);

    $pdo->commit();

    jsonSuccess([
        'removed_member_id' => $member_id,
        'self_removed' => ($member_id === $user_id),
        'remaining_count' => $remaining_count
    ], $member['username'] . ' wurde aus der Gruppe entfernt');
}
catch (PDOException $e)
{
    if ($pdo->inTransaction())
    {
        $pdo->rollBack();
    }
    jsonError('Fehler beim Entfernen des Mitglieds');
}