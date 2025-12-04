<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$loggedIn = !empty($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true;
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/font.css" />
    <link rel="stylesheet" href="../css/style.css"/>
    <link rel="stylesheet" href="../css/layout.css"/>
    <title>DHBW Chat - Zugriff verweigert</title>
    <link rel="icon" type="image/png" href="../img/favicon.png">
</head>

<body>

<?php include '../components/header_public.php'; ?>

<main class="align-center margin-bottom-1 margin-top-3" id="top">
    <h1 class="align-center margin-bottom-2">403 – Zugriff verweigert</h1>
    <p class="align-center margin-bottom-3">Du hast keine Berechtigung, diese Seite zu öffnen.</p>
    <a href="index.php"><button class="style-bold">Zur Startseite</button></a>
</main>

<footer class="footer-grid">
    <section class="footer-left"></section>
    <section class="footer-center">
        <a class="img-logo-footer" href="#top">
            <img src="../img/DHBW-Banner-Chat-Red.png" class="img-logo-footer" alt="DHBW-Chat Logo">
        </a>
        <a href="legal_notice.php">Impressum</a>
        <a href="help.php">Hilfe</a>
        <a href="privacy.php">Datenschutz</a>
    </section>
</footer>

</body>
</html>
