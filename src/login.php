<?php
session_start();

if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
    header('Location: chat.php');
    exit;
}

// TEMP DETAILS
$users = [
        'admin' => password_hash('admin123', PASSWORD_DEFAULT),
        'test'  => password_hash('test123', PASSWORD_DEFAULT),
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['loggedIn'] = true;
        $_SESSION['username'] = $username;

        header('Location: chat.php');
        exit;
    } else {
        $error = 'Ungültiger Benutzername oder Passwort.';
    }
}
?>


<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/font.css" />
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/layout.css"/>
    <title>DHBW Chat - Anmelden</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
</head>
<body>
<main class="img-background-login center-box" id="top">
    <a href="index.php">
        <img src="img/DHBW-Banner-Chat-Red.png" class="img-logo-login" alt="DHBW-Chat-Logo">
    </a>
    <section class ="popup-box">
    <form method="post" action="login.php">
    <h2>Anmelden</h2>
    <br>
    <p >Benutzername:</p>
    <label class>
        <input type="text" maxlength="30" pattern="[A-Za-z0-9]+" title="Nur Buchstaben und Zahlen erlaubt. Maximal 30 Zeichen." inputmode="text" id=username name="username" placeholder="">
    </label>
    <br>
    <p>Passwort:</p>
    <label>
        <input type="password" minlength="4" maxlength="30" pattern="[A-Za-z0-9]+" title="Nur Buchstaben und Zahlen erlaubt. Minimal 4 Zeichen. Maximal 30 Zeichen."  inputmode="text" id=password name="password" placeholder="" autocomplete="current-password">
    </label>
    <br>
    <br>
        <?php if (!empty($error)): ?>
            <p class="style-bold font-error">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>
    <button type=submit class="style-bold">Weiter</button>
    </form>
    <br>
    <br>
    <h3>oder:</h3>
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
    <p >Gruppencode eingeben:</p>
    <label>
        <input class = "input-small" maxlength="6" pattern="[A-Za-z0-9]+" title="Nur Buchstaben und Zahlen erlaubt." inputmode="text" type="text" id=groupcode name="groupcode" placeholder="* * * * * *">
    </label>
    <br>
    <br>
    <a href="chat.php">
        <button type=submit class="style-bold">Gruppenchat beitreten</button>
    </a>
    </form>
    </section>

</main>
<footer class="footer-grid">
    <section class="footer-left">

    </section>
    <section class="footer-center">
    <a class="img-logo-footer" href="#top"><img src="img/DHBW-Banner-Chat-Red.png" alt="DHBW-CHat logo"
                                                class="img-logo-footer"></a>
    <a href="legal_notice.php">Impressum</a>
    <a href="help.php">Hilfe</a>
    <a href="privacy.php">Datenschutz</a>
    </section>
</footer>
</body>
</html>