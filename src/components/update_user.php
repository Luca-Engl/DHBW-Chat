<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLogin();
$action = $_POST['action'] ?? '';

try
{
    if ($action === 'update_username')
    {
        $new_username = requireNotEmpty($_POST['new_username'] ?? '', 'Benutzername darf nicht leer sein');

        if (!preg_match('/^[A-Za-z0-9]+$/', $new_username))
        {
            jsonError('Benutzername darf nur Buchstaben und Zahlen enthalten');
        }

        if (strlen($new_username) > 30)
        {
            jsonError('Benutzername darf maximal 30 Zeichen haben');
        }

        $stmt = $pdo->prepare("SELECT id FROM `user` WHERE username = ? AND id != ?");
        $stmt->execute(array($new_username, $user_id));

        if ($stmt->fetch())
        {
            jsonError('Benutzername bereits vergeben');
        }

        $stmt = $pdo->prepare("UPDATE `user` SET username = ? WHERE id = ?");
        $stmt->execute(array($new_username, $user_id));

        $_SESSION['username'] = $new_username;

        jsonSuccess(['new_username' => $new_username], 'Benutzername erfolgreich geändert');
    }
    elseif ($action === 'update_email')
    {
        $new_email = requireNotEmpty($_POST['new_email'] ?? '', 'E-Mail darf nicht leer sein');

        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL))
        {
            jsonError('Ungültige E-Mail-Adresse');
        }

        $stmt = $pdo->prepare("SELECT id FROM `user` WHERE email = ? AND id != ?");
        $stmt->execute(array($new_email, $user_id));

        if ($stmt->fetch())
        {
            jsonError('E-Mail bereits vergeben');
        }

        $stmt = $pdo->prepare("UPDATE `user` SET email = ? WHERE id = ?");
        $stmt->execute(array($new_email, $user_id));

        jsonSuccess(['new_email' => $new_email], 'E-Mail erfolgreich geändert');
    }
    elseif ($action === 'update_password')
    {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $new_password_confirm = $_POST['new_password_confirm'] ?? '';

        if (empty($old_password) || empty($new_password) || empty($new_password_confirm))
        {
            jsonError('Bitte fülle alle Felder aus');
        }

        $stmt = $pdo->prepare("SELECT password_hash FROM `user` WHERE id = ?");
        $stmt->execute(array($user_id));
        $user = $stmt->fetch();

        if (!$user || !password_verify($old_password, $user['password_hash']))
        {
            jsonError('Altes Passwort ist falsch');
        }

        if ($new_password !== $new_password_confirm)
        {
            jsonError('Neue Passwörter stimmen nicht überein');
        }

        if (strlen($new_password) < 6)
        {
            jsonError('Neues Passwort muss mindestens 6 Zeichen lang sein');
        }

        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE `user` SET password_hash = ? WHERE id = ?");
        $stmt->execute(array($new_password_hash, $user_id));

        jsonSuccess([], 'Passwort erfolgreich geändert');
    }
    else
    {
        jsonError('Ungültige Aktion');
    }
}
catch (PDOException $e)
{
    jsonError('Ein Fehler ist aufgetreten');
}