<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/font.css" />
    <link rel="stylesheet" href="../css/style.css"/>
    <link rel="stylesheet" href="../css/layout.css"/>
    <title>DHBW Chat - Hilfe</title>
    <link rel="icon" type="image/png" href="../img/favicon.png">
</head>
<body>

<<?php include 'components/header_public.php'; ?>

<main id="top">
    <h1 class="align-center margin-bottom-1 margin-top-3">Hilfe & FAQ</h1>
    <p class="align-center style-bold margin-bottom-3">Antworten auf die
        häufigsten Fragen rund um den DHBW Chat.</p>

    <details class="align-center faq-item">
        <summary class="faq-question">Wie anonym kann ich bleiben?</summary>
        <div class="faq-animator">
            <p class="margin-top-1 margin-bottom-1 faq-content">
                Du brauchst keine E-Mail. Nur einen Wunschnamen.
            </p>
        </div>
    </details>

    <details class="align-center faq-item">
        <summary class="faq-question">Wird meine IP gespeichert?</summary>
        <div class="faq-animator">
            <p class="margin-top-1 margin-bottom-1 faq-content">
                Nur für maximal 24 Stunden, um Spam und Angriffe zu verhindern. Danach wird sie automatisch gelöscht.
            </p>
        </div>
    </details>

    <details class="align-center faq-item">
        <summary class="faq-question">Wie erstelle ich eine Gruppe?</summary>
        <div class="faq-animator">
            <p class="margin-top-1 margin-bottom-1 faq-content">
                Klicke im Menü auf "Neue Gruppe", lade deine Kommilitonen ein und legt direkt los.
            </p>
        </div>
    </details>

    <details class="align-center faq-item">
        <summary class="faq-question">Wie funktioniert der globale Chat?</summary>
        <div class="faq-animator">
            <p class="margin-top-1 margin-bottom-1 faq-content">
                Das ist der offene Treffpunkt für den ganzen Campus. Jeder kann posten. Du kannst ihn jederzeit stummschalten oder verlassen.
            </p>
        </div>
    </details>

    <details class="align-center faq-item">
        <summary class="faq-question">Wie ändere ich mein Theme / Layout?</summary>
        <div class="faq-animator">
            <p class="margin-top-1 margin-bottom-1 faq-content">
                In den Einstellungen unter "Darstellung". Dort kannst du dein Theme, Farben, Icons und die Schriftgröße nach deinem Geschmack anpassen.
            </p>
        </div>
    </details>

    <details class="align-center faq-item">
        <summary class="faq-question">Was tun, wenn ich mein Passwort vergesse?</summary>
        <div class="faq-animator">
            <p class="margin-top-1 margin-bottom-1 faq-content">
                Mit einem registrierten Account klickst du beim Login auf "Passwort vergessen", um ein neues festzulegen. Als Gast brauchst du kein Passwort.
            </p>
        </div>
    </details>

    <details class="align-center faq-item">
        <summary class="faq-question">Wie lösche ich mein Konto?</summary>
        <div class="faq-animator">
            <p class="margin-top-1 margin-bottom-1 faq-content">
                Unter "Einstellungen" &#x279C; "Account löschen" kannst du dein Konto und alle dazugehörigen Daten löschen.
            </p>
        </div>
    </details>

    <details class="align-center faq-item margin-bottom-5">
        <summary class="faq-question">Wie melde ich Missbrauch?</summary>
        <div class="faq-animator">
            <p class="margin-top-1 margin-bottom-1 faq-content">
                Über die Meldetaste bei jeder Nachricht und in jeder Gruppe. Unser Team prüft jede Meldung, um die Community zu schützen.
            </p>
        </div>
    </details>
    <br>

    <h2 class="align-center margin-bottom-1">Wir sind für dich da.</h2>
  
    <section class="flex-center align-stretch gap-5 margin-top-2 margin-bottom-5">
      
        <article class="info-box background padding-3 align-left info-box">
            <p>
                Deine Frage war nicht dabei?<br>
                Schreib uns direkt per E-Mail<br>
                oder im Support-Chat.<br>
                Unser Team hilft dir schnell und zuverlässig.<br>
            </p>
        </article>

        <article class="info-box background padding-3 align-left info-box">
            <p>
                Email: support@beispiel.de<br>
                Chat-Support: ÖFFNEN<br>
                Wir melden uns innerhalb von 24-48 Stunden bei dir.
            </p>
        </article>

    </section>

</main>
<footer class="footer-grid">
    <section class="footer-left">

    </section>
    <section class="footer-center">
    <a class="img-logo-footer" href="#top"><img src="../img/DHBW-Banner-Chat-Red.png" alt="DHBW-CHat logo"
                                                class="img-logo-footer"></a>
    <a href="legal_notice.php">Impressum</a>
    <a href="help.php">Hilfe</a>
    <a href="privacy.php">Datenschutz</a>
    </section>
</footer>
<script src="../js/faq-item_animation.js"></script>
</body>
</html>