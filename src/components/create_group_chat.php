<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/user_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLogin();
$group_name = requireNotEmpty($_POST['group_name'] ?? '', 'Bitte gib einen Gruppennamen ein');
$members = isset($_POST['members']) ? json_decode($_POST['members'], true) : [];

if (empty($members) || !is_array($members))
{
    jsonError('Bitte füge mindestens ein Mitglied hinzu');
}

try
{
    $member_ids = [];

    foreach ($members as $member_input)
    {
        $member_input = trim($member_input);
        if (empty($member_input)) continue;

        $member = findUserByUsernameOrEmail($pdo, $member_input, ['id']);

        if ($member)
        {
            $member_ids[] = $member['id'];
        }
        else
        {
            jsonError('Benutzer "' . $member_input . '" nicht gefunden');
        }
    }

    if (empty($member_ids))
    {
        jsonError('Keine gültigen Mitglieder gefunden');
    }

    if (!in_array($user_id, $member_ids))
    {
        $member_ids[] = $user_id;
    }

    $member_ids = array_unique($member_ids);

    if (count($member_ids) < 2)
    {
        jsonError('Eine Gruppe muss mindestens 2 Mitglieder haben');
    }

    $pdo->beginTransaction();

    $invite_code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

    $stmt = $pdo->prepare("
        INSERT INTO chat (chat_name, chat_type, invite_code, created_at)
        VALUES (?, 'group', ?, NOW())
    ");
    $stmt->execute(array($group_name, $invite_code));

    $chat_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO chat_participant (user_id, chat_id, joined_at)
        VALUES (?, ?, NOW())
    ");

    foreach ($member_ids as $member_id)
    {
        $stmt->execute(array($member_id, $chat_id));
    }

    $pdo->commit();

    jsonSuccess([
        'chat_id' => intval($chat_id),
        'chat_name' => $group_name,
        'invite_code' => $invite_code
    ], 'Gruppe erfolgreich erstellt');
}
catch (PDOException $e)
{
    if ($pdo->inTransaction())
    {
        $pdo->rollBack();
    }
    jsonError('Fehler beim Erstellen der Gruppe');
}