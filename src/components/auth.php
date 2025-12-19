<?php
/**
 * Authentifizierungs-Funktionen
 */

/**
 * Startet Session falls nicht aktiv
 */
function ensureSession()
{
    if (session_status() !== PHP_SESSION_ACTIVE)
    {
        session_start();
    }
}

/**
 * Prüft ob User eingeloggt ist (nur registrierte User)
 * Beendet mit JSON-Fehler wenn nicht eingeloggt
 * 
 * @return int User-ID des eingeloggten Users
 */
function requireLogin()
{
    ensureSession();
    
    if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true)
    {
        echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
        exit;
    }
    
    return $_SESSION['user_id'];
}

/**
 * Prüft ob User eingeloggt ist (registrierte User ODER Gäste)
 * Beendet mit JSON-Fehler wenn weder eingeloggt noch Gast
 * 
 * @return int User-ID des eingeloggten Users oder Gastes
 */
function requireLoginOrGuest()
{
    ensureSession();
    
    if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true)
    {
        if (empty($_SESSION['isGuest']) || empty($_SESSION['user_id']))
        {
            echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
            exit;
        }
    }
    
    return $_SESSION['user_id'];
}

/**
 * Prüft ob aktuelle Session ein Gast mit Chat-Zugang ist
 * 
 * @return array|false Array mit 'user_id' und 'chat_id' oder false
 */
function getGuestAccess()
{
    ensureSession();
    
    if (empty($_SESSION['isGuest']) || empty($_SESSION['guest_chat_id']))
    {
        return false;
    }
    
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'chat_id' => $_SESSION['guest_chat_id']
    ];
}
