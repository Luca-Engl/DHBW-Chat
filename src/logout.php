<?php
require_once 'auth.php';
session_start();

if (empty($_SESSION['loggedIn']) && empty($_SESSION['isGuest'])) {
    header("Location: index.php");
    exit;
}

$currentUser  = $_SESSION['username'] ?? 'Unbekannt';

session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="de" class="chat-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/font.css" />
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/layout.css"/>

    <title>Abmeldung</title>
    <link rel="icon" type="image/png" href="img/favicon.png">

    <meta http-equiv="refresh" content="2;url=index.php">
</head>
<body class="chat-page">
<header class="chat-page-header">
    <nav class="chat-nav-grid margin-right-1">
        <section class="chat-nav-left">
            <a href="index.php">
                <img src="img/DHBW-Banner-Chat-Red.png" class="img-logo-nav" alt="DHBW-Chat-Logo">
            </a>
        </section>
        <section class="chat-nav-center"></section>
        <section class="nav-right">
            <span class="nav-logout margin-right-5" style="color: var(--error-color); font-weight: bold;">
                Abmelden
            </span>

            <span class="nav-username margin-right-5">
                <?php echo htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?>
            </span>

            <span class="nav-settings">
                <img src="img/default-avatar.png" alt="User-Avatar">
            </span>
        </section>
    </nav>
</header>

<main class="img-background-login center-box">
    <section class="popup-box align-center margin-top-3">
        <h2 class="style-bold margin-bottom-3">Du wirst abgemeldet ...</h2>
        <p class="font-secondary margin-bottom-3">
            Bis bald, <?php echo htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?>.
        </p>
        <p class="margin-top-3 font-secondary" style="font-size: 0.85rem;">
            Du wirst zur Startseite weitergeleitet.
        </p>
    </section>
</main>
</body>
</html>
