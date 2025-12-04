<!doctype html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/font.css" />
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/layout.css"/>
    <title>DHBW Chat - Datenschutz</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <script src="js/privacy-box_animation.js"></script>
</head>
<body>

<?php include 'components/header_public.php'; ?>

<main class="padding-5" id="top">

    <h1 class="align-center margin-top-2">Datenschutz</h1>
    <p class="align-center margin-bottom-5">
        Entwickelt für deine private Kommunikation.<br>
        Wir speichern nur, was absolut notwendig ist.<br>
        Und deine Chats liest niemand mit.
    </p>

    <section class="flex-center flex-wrap gap-5 margin-bottom-5 privacy-container">
        <div class="privacy-row">
            <div class="align-center privacy-box background fixed-height-13rem">
                <h2>Was wir brauchen:</h2>
                <ul>
                    <li>Registrierungsdatum</li>
                    <li>Accountinformationen</li>
                    <li>Temporäre IP-Logs zur Abuse-Prevention</li>
                    <li>Verschlüsselte Chat-Inhalte</li>
                </ul>
            </div>

            <div class="align-center privacy-box background fixed-height-13rem">
                <h2>Was bei dir bleibt:</h2>
                <ul class="align-left">
                    <li>Inhalte der Nachrichten</li>
                    <li>Adressbuch mit Kontakten</li>
                    <li>Standortdaten</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="flex-center flex-wrap gap-5 margin-bottom-5 privacy-container">

        <div class="privacy-row">
            <div class="align-center privacy-box background margin-bottom-3 fixed-height-10rem">
                <h2>Ende-zu-Ende-Verschlüsselung</h2>
                <p class="align-left">
                    Alle Chats sind Ende-zu-Ende verschlüsselt. Nur du und deine Gesprächspartner können die Nachrichten lesen. Bei uns liegen nur verschlüsselte Daten.
                </p>
            </div>

            <div class="align-center privacy-box background margin-bottom-3 fixed-height-10rem">
                <h2>Gruppen & Schlüsselverwaltung</h2>
                <p class="align-left">
                    Gruppen werden mit eigenen Verschlüsselungsschlüsseln geschützt. Neue Mitglieder erhalten diese verschlüsselt, alte Mitglieder können keine bereits verschickten Nachrichten sehen.
                </p>
            </div>
        </div>

        <div class="privacy-row">
            <div class="align-center privacy-box background fixed-height-10rem">
                <h2>Deine Rechte & Kontrolle</h2>
                <p class="align-left">
                    Du kannst deine Daten jederzeit in den Einstellungen exportieren oder löschen.
                </p>
            </div>

            <div class="align-center privacy-box background fixed-height-10rem">
                <h2>Sicherheit / Bug Reporting</h2>
                <p class="align-left">
                    Sicherheitslücken können über example.tin25@student.dhbw-heidenheim.de gemeldet werden. Wir prüfen Meldungen schnellstmöglich und danken für Hinweise.
                </p>
            </div>
        </div>

    </section>

</main>

<footer class="footer-grid">
    <section class="footer-left">

    </section>
    <section class="footer-center">
        <a class="img-logo-footer" href="#top"><img src="img/DHBW-Banner-Chat-Red.png" alt="DHBW-Chat logo"
                                                    class="img-logo-footer"></a>
        <a href="legal_notice.php">Impressum</a>
        <a href="help.php">Hilfe</a>
        <a href="privacy.php">Datenschutz</a>
    </section>
</footer>

<script>
    function toggleMobileMenu() {
        const hamburger = document.querySelector('.responsive-nav-hamburger');
        const mobileMenu = document.querySelector('.responsive-nav-mobile-menu');
        hamburger.classList.toggle('active');
        mobileMenu.classList.toggle('active');
    }

    // Schließe Mobile Menu beim Klick auf einen Link
    document.querySelectorAll('.responsive-nav-mobile-menu a').forEach(link => {
        link.addEventListener('click', () => {
            const hamburger = document.querySelector('.responsive-nav-hamburger');
            const mobileMenu = document.querySelector('.responsive-nav-mobile-menu');
            hamburger.classList.remove('active');
            mobileMenu.classList.remove('active');
        });
    });
</script>

</body>
</html>