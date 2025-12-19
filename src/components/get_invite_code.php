<?php
header('Content-Type:  application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db_connect.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

$chat_id = isset($_GET['chat_id']) ? intval($_GET['chat_id']) : 0;

if ($chat_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Chat-ID']);
    exit;
}

try {
    // Prüfen ob User Mitglied ist
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM chat_participant 
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->execute(array($chat_id, $_SESSION['user_id']));

    if ($stmt->fetch()['count'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Kein Zugriff']);
        exit;
    }

    // Invite-Code abrufen
    $stmt = $pdo->prepare("
        SELECT invite_code FROM chat 
        WHERE id = ?  AND chat_type = 'group'
    ");
    $stmt->execute(array($chat_id));
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat || !$chat['invite_code']) {
        echo json_encode(['success' => false, 'message' => 'Kein Einladungscode verfügbar']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'invite_code' => $chat['invite_code']
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Datenbankfehler']);
}