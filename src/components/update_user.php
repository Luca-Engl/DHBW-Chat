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

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try
{
    if ($action === 'update_username')
    {
        $new_username = trim($_POST['new_username'] ?? '');

        if (empty($new_username))
        {
            echo json_encode(['success' => false, 'message' => 'Benutzername darf nicht leer sein']);
            exit;
        }

        if (!preg_match('/^[A-Za-z0-9]+$/', $new_username))
        {
            echo json_encode(['success' => false, 'message' => 'Benutzername darf nur Buchstaben und Zahlen enthalten']);
            exit;
        }

        if (strlen($new_username) > 30)
        {
            echo json_encode(['success' => false, 'message' => 'Benutzername darf maximal 30 Zeichen haben']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM `user` WHERE username = ? AND id != ?");
        $stmt->execute(array($new_username, $user_id));

        if ($stmt->fetch())
        {
            echo json_encode(['success' => false, 'message' => 'Benutzername bereits vergeben']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE `user` SET username = ? WHERE id = ?");
        $stmt->execute(array($new_username, $user_id));

        $_SESSION['username'] = $new_username;

        echo json_encode(['success' => true, 'message' => 'Benutzername erfolgreich geändert', 'new_username' => $new_username]);
    }
    elseif ($action === 'update_email')
    {
        $new_email = trim($_POST['new_email'] ?? '');

        if (empty($new_email))
        {
            echo json_encode(['success' => false, 'message' => 'E-Mail darf nicht leer sein']);
            exit;
        }

        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL))
        {
            echo json_encode(['success' => false, 'message' => 'Ungültige E-Mail-Adresse']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM `user` WHERE email = ? AND id != ?");
        $stmt->execute(array($new_email, $user_id));

        if ($stmt->fetch())
        {
            echo json_encode(['success' => false, 'message' => 'E-Mail bereits vergeben']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE `user` SET email = ? WHERE id = ?");
        $stmt->execute(array($new_email, $user_id));

        echo json_encode(['success' => true, 'message' => 'E-Mail erfolgreich geändert', 'new_email' => $new_email]);
    }
    elseif ($action === 'update_password')
    {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $new_password_confirm = $_POST['new_password_confirm'] ?? '';

        if (empty($old_password) || empty($new_password) || empty($new_password_confirm))
        {
            echo json_encode(['success' => false, 'message' => 'Bitte fülle alle Felder aus']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT password_hash FROM `user` WHERE id = ?");
        $stmt->execute(array($user_id));
        $user = $stmt->fetch();

        if (!$user || !password_verify($old_password, $user['password_hash']))
        {
            echo json_encode(['success' => false, 'message' => 'Altes Passwort ist falsch']);
            exit;
        }

        if ($new_password !== $new_password_confirm)
        {
            echo json_encode(['success' => false, 'message' => 'Neue Passwörter stimmen nicht überein']);
            exit;
        }

        if (strlen($new_password) < 6)
        {
            echo json_encode(['success' => false, 'message' => 'Neues Passwort muss mindestens 6 Zeichen lang sein']);
            exit;
        }

        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE `user` SET password_hash = ? WHERE id = ?");
        $stmt->execute(array($new_password_hash, $user_id));

        echo json_encode(['success' => true, 'message' => 'Passwort erfolgreich geändert']);
    }
    else
    {
        echo json_encode(['success' => false, 'message' => 'Ungültige Aktion']);
    }
}
catch (PDOException $e)
{
    echo json_encode(['success' => false, 'message' => 'Ein Fehler ist aufgetreten']);
}
