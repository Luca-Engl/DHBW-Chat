<?php
$host = 'localhost';
$username = 'root';
$password = 'chat';
$database = 'dhbw-chat';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "PDO-Verbindung erfolgreich zur Datenbank '$database'!";

} catch (PDOException $e) {
    die("Verbindung fehlgeschlagen: " . $e->getMessage());
}
?>