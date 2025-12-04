<?php
// 1. Aktuellen Dateinamen ermitteln (z.B. "index.php" oder "privacy.php")
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<header>
    <nav class="nav-grid margin-left-10 margin-right-10">
        <section class="nav-left">
            <a href="index.php" class="nav-left-a">
                <img src="img/DHBW-Banner-Chat-Red.png" class="img-logo-nav margin-left-3" alt="DHBW-Chat-Logo">
            </a>

            <a href="privacy.php" class="responsive-nav-desktop-link <?= ($currentPage == 'privacy.php') ? 'active-nav' : '' ?>">
                Datenschutz
            </a>
            <a href="help.php" class="responsive-nav-desktop-link <?= ($currentPage == 'help.php') ? 'active-nav' : '' ?>">
                Hilfe
            </a>
        </section>

        <section class="nav-center">
            <button class="responsive-nav-hamburger" id="responsiveNavHamburger">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </section>

        <section class="nav-right margin-right-5 margin-top-3 margin-bottom-3">
            <?php if ($currentPage != 'login.php'): ?>
                <a href="login.php">
                    <button class="style-bold">Anmelden</button>
                </a>
            <?php endif; ?>
        </section>
    </nav>

    <div class="responsive-nav-mobile-menu" id="responsiveNavMobileMenu">
        <a href="privacy.php" class="<?= ($currentPage == 'privacy.php') ? 'active-nav' : '' ?>">Datenschutz</a>
        <a href="help.php" class="<?= ($currentPage == 'help.php') ? 'active-nav' : '' ?>">Hilfe</a>
    </div>
</header>