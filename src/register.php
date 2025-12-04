<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/font.css" />
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/layout.css"/>
    <title>DHBW Chat - Registrierung</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
</head>
<body>
<main class="img-background-login center-box" id="top">
    <a href="index.php">
        <img src="img/DHBW-Banner-Chat-Red.png" class="img-logo-login" alt="DHBW-Chat-Logo">
    </a>
    <section class ="popup-box">
    <h2>Registrieren</h2>
    <br>
    <p>Benutzername:</p>
    <label>
        <input type="text" maxlength="30" pattern="[A-Za-z0-9]+" title="Nur Buchstaben und Zahlen erlaubt. Maximal 30 Zeichen." inputmode="text" id=username name="username" placeholder="">
    </label>
    <br>
    <p>DHBW E-Mail Adresse:</p>
    <label>
        <input type="email" inputmode="email" id=displayname name="displayname" placeholder="">
    </label>
    <br><br>
    <!--<p>Passwort eingeben:</p>
    <label>
        <input type="password" id= password name="password" placeholder="" autocomplete="current-password">
    </label>
    <br>
    <p>Passwort wiederholen:</p>
    <label>
        <input type="password" id= password_rep name="password_rep" placeholder="" autocomplete="current-password">
    </label>
    <br><br>-->
    <p>Studiengang:</p>
    <label>
    <select class = "select-small" id="faculty" name="faculty">
        <option value="" selected hidden="">Fakultät ...</option>
        <option value="T">Technik</option>
        <option value="W">Wirtschaft</option>
        <option value="S">Sozialwesen</option>
        <option value="G">Gesundheit</option>
        <option value="A">Sonstige</option>
    </select>
</label>

    <label>
        <select class = "select-small" id="cursus" name="cursus">
            <option value="" selected hidden="">Kurs ...</option>
            <option value="INF">Angewandte Informatik</option>
            <option value="MB">Maschinenbau</option>
            <option value="MT">Mechatronik</option>
            <option value="WIW">Wirtschaftsingenieurwesen</option>
        </select>
    </label>

    <label>
        <select class = "select-small" id="year" name="year">
            <option value="" selected hidden="">Jahr ...</option>
            <option value="2025">2025</option>
            <option value="2024">2024</option>
            <option value="2023">2023</option>
            <option value="2022">2022</option>
            <option value="2021">2021</option>
            <option value="2020">2020</option>
        </select>
    </label>


    <br><br>
    <a href="chat.php">
        <button>Registrieren</button>
    </a>
    <br>
    <br>
    <h3>oder</h3>
    <br>
    <a href=login.php>
        <button class="button-secondary">Zurück zur Anmeldung</button>
    </a>
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