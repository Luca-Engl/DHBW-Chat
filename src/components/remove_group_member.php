<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db_connect.php';

if (session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true)
{
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

$user_id   = intval($_SESSION['user_id'] ?? 0);
$chat_id   = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;
$member_id = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;

if ($user_id <= 0 || $chat_id <= 0 || $member_id <= 0)
{
    echo json_encode(['success' => false, 'message' => 'Ungültige Parameter']);
    exit;
}

try
{
    // In einer Transaktion arbeiten, damit "Remove" und das Zählen der Rest-Mitglieder konsistent ist.
    $pdo->beginTransaction();

    // Zugriff prüfen: anfragender Nutzer muss Mitglied sein
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS count
        FROM chat_participant
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->execute(array($chat_id, $user_id));
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($access) || intval($access['count']) === 0)
    {
        if ($pdo->inTransaction())
        {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Du bist nicht Mitglied dieser Gruppe']);
        exit;
    }

    // Nur Gruppen erlauben
    $stmt = $pdo->prepare("
        SELECT chat_type
        FROM chat
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute(array($chat_id));
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat)
    {
        if ($pdo->inTransaction())
        {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Chat nicht gefunden']);
        exit;
    }

    if ($chat['chat_type'] !== 'group')
    {
        if ($pdo->inTransaction())
        {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Dies ist keine Gruppe']);
        exit;
    }

    // Prüfen, ob das Zielmitglied in der Gruppe ist, und Username laden
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
        if ($pdo->inTransaction())
        {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Mitglied nicht in der Gruppe gefunden']);
        exit;
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
    $remaining_count = isset($remaining['count']) ? intval($remaining['count']) : 0;

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $member['username'] . ' wurde aus der Gruppe entfernt',
        'removed_member_id' => $member_id,
        'self_removed' => ($member_id === $user_id),
        'remaining_count' => $remaining_count
    ]);
}
catch (PDOException $e)
{
    if ($pdo->inTransaction())
    {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Entfernen des Mitglieds'
    ]);
}
