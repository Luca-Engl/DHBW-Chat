<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['loggedIn']) && empty($_SESSION['isGuest'])) {
    header("Location: index.php");
    exit;
}

$currentUser  = $_SESSION['username'] ?? 'Unbekannt';
if (! empty($_SESSION['isGuest']) && ! empty($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM `user` WHERE id = ?  AND username LIKE 'Guest_%'");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        error_log("Error deleting guest user: " . $e->getMessage());
    }
}

session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="de" class="chat-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/font.css" />
    <link rel="stylesheet" href="../css/style.css"/>
    <link rel="stylesheet" href="../css/layout.css"/>

    <title>DHBW Chat - Abmelden</title>
    <link rel="icon" type="image/png" href="../img/favicon.png">

    <meta http-equiv="refresh" content="2;url=index.php">
</head>
<body class="chat-page">
<header class="chat-page-header">
    <nav class="chat-nav-grid margin-right-1">
        <section class="chat-nav-left">
            <a href="index.php">
                <img src="../img/DHBW-Banner-Chat-Red.png" class="img-logo-nav" alt="DHBW-Chat-Logo">
            </a>
        </section>
        <section class="chat-nav-center"></section>
        <section class="nav-right">
            <span class="nav-logout margin-right-5 font-secondary">
                Abmelden
            </span>

            <span class="nav-username margin-right-5 style-bold ">
                <?php echo htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?>
            </span>

            <span class="nav-settings">
                <img src="../img/default-avatar.png" alt="User-Avatar">
            </span>
        </section>
    </nav>
</header>
<body>
<main class="img-background-login center-box">
    <a href="./index.php">
        <img src="../img/DHBW-Banner-Chat-Red.png" class="margin-top-10 img-logo-login" alt="DHBW-Chat-Logo">
    </a>
    <section class="popup-box">
        <h2 class="margin-bottom-5">Abmeldung wird ausgeführt ...</h2>
        <h3 class="margin-bottom-3">
            Bis bald, <?php echo htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?>.
        </h3>
        <p class="margin-top-3 font-secondary">
            Du wirst in Kürze automatisch zur Startseite weitergeleitet.
        </p>
    </section>
</main>
</body>
</html>