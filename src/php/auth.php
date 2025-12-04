<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['loggedIn']) && empty($_SESSION['isGuest'])) {
    header("Location: 403.php");
    exit;
}
