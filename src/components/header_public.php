<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$loggedIn = !empty($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true;
?>

<header>
    <nav class="nav-grid margin-left-10 margin-right-10">
        <section class="nav-left">
            <a href="index.php" class="nav-left-a">
                <img src="../img/DHBW-Banner-Chat-Red.png" class="img-logo-nav margin-left-3" alt="DHBW-Chat-Logo">
            </a>
            <a href="privacy.php" class="responsive-nav-desktop-link <?= ($currentPage == 'privacy.php') ? 'active-nav' : '' ?>">
                Datenschutz
            </a>
            <a href="help.php" class="responsive-nav-desktop-link <?= ($currentPage == 'help.php') ? 'active-nav' : '' ?>">
                Hilfe
            </a>
        </section>

        <section class="nav-center">
            <button class="responsive-nav-hamburger" id="responsiveNavHamburger" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </section>

        <section class="nav-right margin-right-5 margin-top-3 margin-bottom-3">
            <?php if ($currentPage != 'login.php'): ?>
                <a href="<?= $loggedIn ? 'chat.php' : 'login.php' ?>">
                    <button class = "style-bold"><?php echo $loggedIn ? 'Angemeldet' : 'Anmelden'; ?></button>
                </a>
            <?php endif; ?>
        </section>
    </nav>

    <div class="responsive-nav-mobile-menu" id="responsiveNavMobileMenu">
        <a href="privacy.php" class="<?= ($currentPage == 'privacy.php') ? 'active-nav' : '' ?>">Datenschutz</a>
        <a href="help.php" class="<?= ($currentPage == 'help.php') ? 'active-nav' : '' ?>">Hilfe</a>
    </div>
</header>

<script>
    function toggleMobileMenu() {
        const hamburger = document.querySelector('.responsive-nav-hamburger');
        const mobileMenu = document.querySelector('.responsive-nav-mobile-menu');
        hamburger.classList.toggle('active');
        mobileMenu.classList.toggle('active');
    }

    document.querySelectorAll('.responsive-nav-mobile-menu a').forEach(link => {
        link.addEventListener('click', () => {
            document.querySelector('.responsive-nav-hamburger').classList.remove('active');
            document.querySelector('.responsive-nav-mobile-menu').classList.remove('active');
        });
    });
</script>