<?php
/**
 * User Hilfsfunktionen
 */

/**
 * Sucht einen User anhand von Username oder E-Mail
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param string $input Username oder E-Mail
 * @param array $fields Welche Felder zurückgegeben werden sollen (default: id, username)
 * @return array|false User-Daten oder false wenn nicht gefunden
 */
function findUserByUsernameOrEmail($pdo, $input, $fields = ['id', 'username'])
{
    $input = trim($input);
    
    if (empty($input))
    {
        return false;
    }
    
    $fieldList = implode(', ', $fields);
    
    if (filter_var($input, FILTER_VALIDATE_EMAIL))
    {
        $stmt = $pdo->prepare("SELECT {$fieldList} FROM `user` WHERE email = ?");
    }
    else
    {
        $stmt = $pdo->prepare("SELECT {$fieldList} FROM `user` WHERE username = ?");
    }
    
    $stmt->execute(array($input));
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Sucht einen User anhand von Username oder E-Mail
 * Beendet mit JSON-Fehler wenn nicht gefunden
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param string $input Username oder E-Mail
 * @param array $fields Welche Felder zurückgegeben werden sollen
 * @param string $error_message Optionale Fehlermeldung
 * @return array User-Daten
 */
function requireUserByUsernameOrEmail($pdo, $input, $fields = ['id', 'username'], $error_message = null)
{
    $user = findUserByUsernameOrEmail($pdo, $input, $fields);
    
    if (!$user)
    {
        $message = $error_message ?? 'Benutzer nicht gefunden';
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
    
    return $user;
}

/**
 * Holt User-Daten anhand der ID
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param int $user_id User-ID
 * @param array $fields Welche Felder zurückgegeben werden sollen
 * @return array|false User-Daten oder false
 */
function getUserById($pdo, $user_id, $fields = ['id', 'username', 'email'])
{
    $fieldList = implode(', ', $fields);
    
    $stmt = $pdo->prepare("SELECT {$fieldList} FROM `user` WHERE id = ?");
    $stmt->execute(array($user_id));
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Prüft ob ein User bereits Mitglied eines Chats ist
 * 
 * @param PDO $pdo Datenbankverbindung
 * @param int $chat_id Chat-ID
 * @param int $user_id User-ID
 * @return bool true wenn Mitglied
 */
function isUserChatMember($pdo, $chat_id, $user_id)
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM chat_participant
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->execute(array($chat_id, $user_id));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] > 0;
}
