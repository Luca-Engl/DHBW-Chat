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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '')
    {
        $error = 'Bitte fülle alle Felder aus.';
    }
    elseif (!preg_match('/^[A-Za-z0-9]+$/', $username))
    {
        $error = 'Der Benutzername darf nur Buchstaben und Zahlen enthalten.';
    }
    elseif (strlen($username) > 30)
    {
        $error = 'Der Benutzername darf maximal 30 Zeichen haben.';
    }
    elseif (strlen($password) < 6 || strlen($password) > 30)
    {
        $error = 'Das Passwort muss zwischen 6 und 30 Zeichen lang sein.';
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
<main class="img-background-login center-box">
    <a href="./index.php">
        <img src="../img/DHBW-Banner-Chat-Red.png" class="img-logo-login" alt="DHBW-Chat-Logo">
    </a>
    <section class="popup-box">
        <form method="post" action="login.php" id="loginForm" novalidate>
            <h2>Anmelden</h2>

            <div id="pageError"
                 class="error-message margin-bottom-3<?php echo empty($error) ? ' is-hidden' : ''; ?>"
                 role="alert"
                 aria-live="polite">
                <span id="pageErrorText"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="margin-bottom-3"></div>

            <p>Benutzername:</p>
            <label>
                <input type="text"
                       maxlength="30"
                       inputmode="text"
                       id="username"
                       name="username"
                       placeholder=""
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            </label>

            <div class="margin-bottom-3"></div>

            <p>Passwort:</p>
            <label>
                <input type="password"
                       maxlength="30"
                       inputmode="text"
                       id="password"
                       name="password"
                       placeholder=""
                       autocomplete="current-password">
            </label>

            <div class="margin-bottom-5"></div>

            <button type="submit" class="style-bold">Anmelden</button>
        </form>

        <div class="margin-bottom-5"></div>

        <h3>oder</h3>

        <div class="margin-bottom-5"></div>

        <a href="register.php">
            <button class="button-secondary">Account erstellen</button>
        </a>

        <div class="margin-bottom-7"></div>

        <form method="get" action="chat.php" id="guestForm" novalidate>
            <h2>Gruppenchat als Gast beitreten:</h2>

            <div class="margin-bottom-3"></div>

            <p>Gruppencode eingeben:</p>
            <label>
                <input class="input-small"
                       maxlength="6"
                       inputmode="text"
                       type="text"
                       id="groupcode"
                       name="groupcode"
                       placeholder="* * * * * *">
            </label>

            <div class="margin-bottom-5"></div>

            <button type="submit" class="style-bold">Gruppenchat beitreten</button>
        </form>
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

<script>
    (function () {
        const box = document.getElementById('pageError');
        const text = document.getElementById('pageErrorText');

        let timer = null;

        function hide() {
            if (!box) return;
            box.classList.add('is-hidden');
        }

        function show(message) {
            if (!box || !text) return;
            text.textContent = message;
            box.classList.remove('is-hidden');

            if (timer) window.clearTimeout(timer);
            timer = window.setTimeout(hide, 5000);
        }

        window.hidePageError = hide;
        window.showPageError = show;

        if (text && text.textContent.trim() !== '') {
            timer = window.setTimeout(hide, 5000);
        }

        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function (e) {
                const username = (document.getElementById('username')?.value || '').trim();
                const password = document.getElementById('password')?.value || '';

                if (username === '' || password === '') {
                    e.preventDefault();
                    show('Bitte fülle alle Felder aus.');
                    return;
                }
                if (!/^[A-Za-z0-9]+$/.test(username)) {
                    e.preventDefault();
                    show('Benutzername darf nur Buchstaben und Zahlen enthalten.');
                    return;
                }
                if (username.length > 30) {
                    e.preventDefault();
                    show('Benutzername darf maximal 30 Zeichen haben.');
                    return;
                }
                if (password.length < 6 || password.length > 30) {
                    e.preventDefault();
                    show('Passwort muss zwischen 6 und 30 Zeichen lang sein.');
                    return;
                }
            });
        }

        const guestForm = document.getElementById('guestForm');
        if (guestForm) {
            guestForm.addEventListener('submit', function (e) {
                const code = (document.getElementById('groupcode')?.value || '').trim();

                if (code === '') {
                    e.preventDefault();
                    show('Bitte gib einen Gruppencode ein.');
                    return;
                }
                if (!/^[A-Za-z0-9]{6}$/.test(code)) {
                    e.preventDefault();
                    show('Der Gruppencode ist ungültig.);
                    return;
                }
            });
        }
    })();
</script>
</body>
</html>
