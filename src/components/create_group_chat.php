<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

/** @var PDO $pdo */

if (session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true)
{
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

$user_id = $_SESSION['user_id'];
$group_name = isset($_POST['group_name']) ? trim($_POST['group_name']) : '';
$members = isset($_POST['members']) ? json_decode($_POST['members'], true) : [];

if (empty($group_name))
{
    echo json_encode(['success' => false, 'message' => 'Bitte gib einen Gruppennamen ein']);
    exit;
}

if (empty($members) || !is_array($members))
{
    echo json_encode(['success' => false, 'message' => 'Bitte füge mindestens ein Mitglied hinzu']);
    exit;
}

try
{
    $member_ids = [];

    foreach ($members as $member_input)
    {
        $member_input = trim($member_input);

        if (empty($member_input))
        {
            continue;
        }

        if (filter_var($member_input, FILTER_VALIDATE_EMAIL))
        {
            $stmt = $pdo->prepare("SELECT id FROM `user` WHERE email = ?");
            $stmt->execute(array($member_input));
        }
        else
        {
            $stmt = $pdo->prepare("SELECT id FROM `user` WHERE username = ?");
            $stmt->execute(array($member_input));
        }

        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($member)
        {
            $member_ids[] = $member['id'];
        }
        else
        {
            echo json_encode(['success' => false, 'message' => 'Benutzer "' . $member_input . '" nicht gefunden']);
            exit;
        }
    }

    if (empty($member_ids))
    {
        echo json_encode(['success' => false, 'message' => 'Keine gültigen Mitglieder gefunden']);
        exit;
    }

    if (!in_array($user_id, $member_ids))
    {
        $member_ids[] = $user_id;
    }

    $member_ids = array_unique($member_ids);

    if (count($member_ids) < 2)
    {
        echo json_encode(['success' => false, 'message' => 'Eine Gruppe muss mindestens 2 Mitglieder haben']);
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO chat (chat_name, chat_type, created_at)
        VALUES (?, 'group', NOW())
    ");
    $stmt->execute(array($group_name));

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

    echo json_encode([
        'success' => true,
        'message' => 'Gruppe erfolgreich erstellt',
        'chat_id' => intval($chat_id),
        'chat_name' => $group_name
    ]);
}
catch (PDOException $e)
{
    if ($pdo->inTransaction())
    {
        $pdo->rollBack();
    }
    error_log("CREATE GROUP ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Erstellen der Gruppe'
    ]);
}
?>