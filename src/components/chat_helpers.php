<?php
/**
 * Chat Hilfsfunktionen
 */

/**
 * Validiert eine Chat-ID
 * Beendet mit JSON-Fehler wenn ungültig
 * 
 * @param mixed $chat_id Die zu validierende Chat-ID
 * @return int Die validierte Chat-ID als Integer
 */
function validateChatId($chat_id)
{
    $chat_id = intval($chat_id);
    
    if ($chat_id <= 0)
    {
        echo json_encode(['success' => false, 'message' => 'Ungültige Chat-ID']);
        exit;
    }
    
    return $chat_id;
}

/**
 * Validiert eine Nachrichten-ID
 * Beendet mit JSON-Fehler wenn ungültig
 * 
 * @param mixed $message_id Die zu validierende Nachrichten-ID
 * @return int Die validierte Nachrichten-ID als Integer
 */
function validateMessageId($message_id)
{
    $message_id = intval($message_id);
    
    if ($message_id <= 0)
    {
        echo json_encode(['success' => false, 'message' => 'Ungültige Nachrichten-ID']);
        exit;
    }
    
    return $message_id;
}

/**
 * Validiert eine Notiz-ID
 * Beendet mit JSON-Fehler wenn ungültig
 * 
 * @param mixed $note_id Die zu validierende Notiz-ID
 * @return int Die validierte Notiz-ID als Integer
 */
function validateNoteId($note_id)
{
    $note_id = intval($note_id);
    
    if ($note_id <= 0)
    {
        echo json_encode(['success' => false, 'message' => 'Ungültige Notiz-ID']);
        exit;
    }
    
    return $note_id;
}

/**
 * Prüft ob ein User Zugriff auf einen Chat hat (ist Teilnehmer)
 * Beendet mit JSON-Fehler wenn kein Zugriff
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param int $chat_id Chat-ID
 * @param int $user_id User-ID
 * @param string $error_message Optionale Fehlermeldung
 * @return bool true wenn Zugriff vorhanden
 */
function requireChatAccess($pdo, $chat_id, $user_id, $error_message = 'Kein Zugriff auf diesen Chat')
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM chat_participant
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->execute(array($chat_id, $user_id));
    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($access['count'] == 0)
    {
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    }
    
    return true;
}

/**
 * Prüft ob ein Chat existiert und gibt Chat-Daten zurück
 * Beendet mit JSON-Fehler wenn nicht gefunden
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param int $chat_id Chat-ID
 * @return array Chat-Daten (id, chat_name, chat_type, invite_code)
 */
function requireChatExists($pdo, $chat_id)
{
    $stmt = $pdo->prepare("
        SELECT id, chat_name, chat_type, invite_code
        FROM chat
        WHERE id = ?
    ");
    $stmt->execute(array($chat_id));
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat)
    {
        echo json_encode(['success' => false, 'message' => 'Chat nicht gefunden']);
        exit;
    }
    
    return $chat;
}

/**
 * Prüft ob ein Chat eine Gruppe ist
 * Beendet mit JSON-Fehler wenn keine Gruppe
 * 
 * @param array $chat Chat-Daten mit 'chat_type' Schlüssel
 * @return bool true wenn Gruppe
 */
function requireGroupChat($chat)
{
    if (!isset($chat['chat_type']) || $chat['chat_type'] !== 'group')
    {
        echo json_encode(['success' => false, 'message' => 'Dies ist keine Gruppe']);
        exit;
    }
    
    return true;
}

/**
 * Kombinierte Prüfung: Chat existiert, ist Gruppe, User hat Zugriff
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param int $chat_id Chat-ID
 * @param int $user_id User-ID
 * @return array Chat-Daten
 */
function requireGroupAccess($pdo, $chat_id, $user_id)
{
    requireChatAccess($pdo, $chat_id, $user_id, 'Du bist nicht Mitglied dieser Gruppe');
    $chat = requireChatExists($pdo, $chat_id);
    requireGroupChat($chat);
    
    return $chat;
}
