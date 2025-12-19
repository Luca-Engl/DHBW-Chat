<?php
/**
 * API Initialisierung
 * Setzt JSON-Header, deaktiviert Error-Ausgabe und lädt DB-Connection
 */

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db_connect.php';
