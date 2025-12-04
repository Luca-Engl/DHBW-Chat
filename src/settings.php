<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/font.css" />
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/layout.css"/>
    <title>DHBW Chat - Einstellungen</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
</head>
<body>
<header class="chat-page-header">
    <nav class="chat-nav-grid margin-right-1">
        <section class="chat-nav-left">
            <a href="index.php">
                <img src="img/DHBW-Banner-Chat-Red.png" class="img-logo-nav" alt="DHBW-Chat-Logo">
            </a>
        </section>
        <section class="chat-nav-center"></section>
        <section class="nav-right">
            <p class="margin-right-5">ExampleUser</p>
            <a href="#" onclick="openSettings()" class="nav-settings">
                <img src="img/default-avatar.png" alt="User-Avatar">
            </a>
        </section>
    </nav>
</header>

<main class="center-box">

    <section class="popup-box align-left margin-top-5">
        <h2 class="style-bold align-center margin-bottom-5">Einstellungen</h2>

        <section class="margin-bottom-3">
            <label for="profile-picture" class="style-bold">Profilbild:</label>
            <img id="avatar-preview" src="img/default-avatar.png" alt="Avatar Vorschaubild" style="width:60px; height:60px; border-radius:50%; margin-top:5px;">
            <input type="file" id="profile-picture" accept="image/*">
            </section>

        <section class="margin-bottom-3">
            <label class="style-bold">Username:</label>
            <p>
                <span id="username-display">Luca Engl</span>
                <button type="button" class="button-secondary">Bearbeiten</button>
            </p>
        </section>

        <section class="margin-bottom-3">
            <label class="style-bold">Email:</label>
            <p>
                <span id="email-display">engll.tin25@student.dhbw-heidenheim.de</span>
                <button type="button" class="button-secondary">Bearbeiten</button>
            </p>
        </section>

        <section class="margin-bottom-3">
            <label class="style-bold">Passwort:</label>
            <p>
                <span id="password-display">••••••••</span>
                <button type="button" class="button-secondary">Bearbeiten</button>
            </p>
        </section>

        <section class="margin-top-5">
            <button type="button">Speichern</button>
        </section>
    </section>

</main>
</body>
</html>