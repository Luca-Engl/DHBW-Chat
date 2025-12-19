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

$user_id  = intval($_SESSION['user_id'] ?? 0);
$chat_id  = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;
$chat_name = isset($_POST['chat_name']) ? trim($_POST['chat_name']) : '';

if ($user_id <= 0 || $chat_id <= 0)
{
    echo json_encode(['success' => false, 'message' => 'Ungültige Parameter']);
    exit;
}

if ($chat_name === '')
{
    echo json_encode(['success' => false, 'message' => 'Bitte gib einen Gruppennamen ein']);
    exit;
}

if (mb_strlen($chat_name, 'UTF-8') > 15)
{
    echo json_encode(['success' => false, 'message' => 'Gruppenname darf maximal 15 Zeichen haben']);
    exit;
}

try
{
    // Zugriff prüfen: anfragender Nutzer muss Mitglied sein
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM chat_participant WHERE chat_id = ? AND user_id = ?");
    $stmt->execute(array($chat_id, $user_id));
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($access) || intval($access['count']) === 0)
    {
        echo json_encode(['success' => false, 'message' => 'Du bist nicht Mitglied dieser Gruppe']);
        exit;
    }

    // Nur Gruppen erlauben
    $stmt = $pdo->prepare("SELECT chat_type FROM chat WHERE id = ? LIMIT 1");
    $stmt->execute(array($chat_id));
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat)
    {
        echo json_encode(['success' => false, 'message' => 'Chat nicht gefunden']);
        exit;
    }

    if (($chat['chat_type'] ?? '') !== 'group')
    {
        echo json_encode(['success' => false, 'message' => 'Dies ist keine Gruppe']);
        exit;
    }

    // Update
    $stmt = $pdo->prepare("UPDATE chat SET chat_name = ? WHERE id = ?");
    $stmt->execute(array($chat_name, $chat_id));

    echo json_encode([
        'success' => true,
        'message' => 'Gruppenname aktualisiert',
        'chat_id' => $chat_id,
        'chat_name' => $chat_name
    ]);
}
catch (PDOException $e)
{
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Aktualisieren des Gruppennamens'
    ]);
}
