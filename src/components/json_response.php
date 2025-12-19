<?php
/**
 * JSON Response Hilfsfunktionen
 */

/**
 * Sendet eine erfolgreiche JSON-Antwort und beendet das Script
 * 
 * @param array $data Zusätzliche Daten für die Antwort
 * @param string|null $message Optionale Erfolgsmeldung
 */
function jsonSuccess($data = [], $message = null)
{
    $response = ['success' => true];
    
    if ($message !== null)
    {
        $response['message'] = $message;
    }
    
    echo json_encode(array_merge($response, $data));
    exit;
}

/**
 * Sendet eine Fehler JSON-Antwort und beendet das Script
 * 
 * @param string $message Fehlermeldung
 * @param array $data Zusätzliche Daten für die Antwort
 */
function jsonError($message, $data = [])
{
    $response = ['success' => false, 'message' => $message];
    echo json_encode(array_merge($response, $data));
    exit;
}

/**
 * Validiert dass ein Pflichtfeld nicht leer ist
 * Beendet mit JSON-Fehler wenn leer
 * 
 * @param mixed $value Der zu prüfende Wert
 * @param string $error_message Fehlermeldung wenn leer
 * @return mixed Der getrimmte Wert
 */
function requireNotEmpty($value, $error_message)
{
    $value = is_string($value) ? trim($value) : $value;
    
    if (empty($value))
    {
        jsonError($error_message);
    }
    
    return $value;
}
