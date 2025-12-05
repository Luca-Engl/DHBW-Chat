<?php
require_once __DIR__ . '/../components/db_connect.php';

/** @var PDO $pdo */

if (session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true)
{
    header('Location: chat.php');
    exit;
}

// DEBUG: Force-Login
if (isset($_GET['forceLogin']) && $_GET['forceLogin'] === '1')
{
    $_SESSION['loggedIn'] = true;
    $_SESSION['username'] = 'SYSTEM';
    header('Location: chat.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password))
    {
        $error = 'Bitte fülle alle Felder aus.';
    }
    else
    {
        try
        {
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM `user` WHERE username = ?");
            $stmt->execute(array($username));
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash']))
            {
                $_SESSION['loggedIn'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                header('Location: chat.php');
                exit;
            }
            else
            {
                $error = 'Ungültiger Benutzername oder Passwort.';
            }
        }
        catch (PDOException $e)
        {
            error_log("Login error: " . $e->getMessage());
            $error = 'Ein Fehler ist aufgetreten. Bitte versuche es später erneut.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/font.css" />
    <link rel="stylesheet" href="../css/style.css"/>
    <link rel="stylesheet" href="../css/layout.css"/>
    <title>DHBW Chat - Anmelden</title>
    <link rel="icon" type="image/png" href="../img/favicon.png">
</head>
<body>
<main class="img-background-login center-box" id="top">
    <a href="../index.php">
        <img src="../img/DHBW-Banner-Chat-Red.png" class="img-logo-login" alt="DHBW-Chat-Logo">
    </a>
    <section class="popup-box">
        <form method="post" action="login.php">
            <h2>Anmelden</h2>
            <br>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <p>Benutzername:</p>
            <label>
                <input type="text"
                       maxlength="30"
                       pattern="[A-Za-z0-9]+"
                       title="Nur Buchstaben und Zahlen erlaubt. Maximal 30 Zeichen."
                       inputmode="text"
                       id="username"
                       name="username"
                       placeholder=""
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       required>
            </label>
            <br>

            <p>Passwort:</p>
            <label>
                <input type="password"
                       minlength="6"
                       maxlength="30"
                       title="Mindestens 6 Zeichen."
                       inputmode="text"
                       id="password"
                       name="password"
                       placeholder=""
                       autocomplete="current-password"
                       required>
            </label>
            <br>
            <br>

            <button type="submit" class="style-bold">Anmelden</button>
        </form>

        <br>
        <br>
        <h3>oder</h3>
        <br>
        <a href="register.php">
            <button class="button-secondary">Account erstellen</button>
        </a>

        <br>
        <br>
        <br>

        <form method="get" action="chat.php">
            <h2>Gruppenchat als Gast beitreten:</h2>
            <br>
            <p>Gruppencode eingeben:</p>
            <label>
                <input class="input-small"
                       maxlength="6"
                       pattern="[A-Za-z0-9]+"
                       title="Nur Buchstaben und Zahlen erlaubt."
                       inputmode="text"
                       type="text"
                       id="groupcode"
                       name="groupcode"
                       placeholder="* * * * * *">
            </label>
            <br>
            <br>
            <button type="submit" class="style-bold">Gruppenchat beitreten</button>
        </form>

        <br><br>
        <a href="login.php?forceLogin=1">
            <button type="button" class="button-secondary">FORCE LOGIN</button>
        </a>
    </section>
</main>

<footer class="footer-grid">
    <section class="footer-left"></section>
    <section class="footer-center">
        <a class="img-logo-footer" href="#top">
            <img src="../img/DHBW-Banner-Chat-Red.png" alt="DHBW-Chat logo" class="img-logo-footer">
        </a>
        <a href="legal_notice.php">Impressum</a>
        <a href="help.php">Hilfe</a>
        <a href="privacy.php">Datenschutz</a>
    </section>
</footer>
</body>
</html>