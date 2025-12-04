<?php
require_once 'auth.php';

session_start();

if (!empty($_GET['groupcode'])) {
    $groupcode = preg_replace('/[^A-Za-z0-9]/', '', $_GET['groupcode']);

    if ($groupcode === '') {
        header('Location: login.php');
        exit;
    }

    $_SESSION['isGuest']   = true;
    $_SESSION['groupcode'] = $groupcode;

    if (empty($_SESSION['username'])) {
        try {
            $randSuffix = random_int(100, 999);
        } catch (Exception $e) {
            $randSuffix = mt_rand(100, 999);
        }
        $_SESSION['username'] = 'Gast_' . substr($groupcode, 0, 3) . '_' . $randSuffix;
    }
}

$loggedIn = !empty($_SESSION['loggedIn']);
$isGuest  = !empty($_SESSION['isGuest']);

$currentUser  = $_SESSION['username'] ?? 'Unbekannt';
$currentGroup = $_SESSION['groupcode'] ?? null;
?>


<!DOCTYPE html>
<html lang="de" class="chat-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/font.css" />
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/layout.css"/>

    <title>DHBW Chat - Chatbereich</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
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
            <?php if ($loggedIn): ?>
                <a href="logout.php" class="nav-logout margin-right-5 style-bold">
                    Abmelden
                </a>
            <?php endif; ?>

            <a href="#" class="nav-username margin-right-5" onclick="openSettings(); return false;">
                <?php echo htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?>
            </a>
            <a href="#" onclick="openSettings(); return false;" class="nav-settings">
                <img src="img/default-avatar.png" alt="User-Avatar">
            </a>
        </section>
    </nav>
</header>

<main class="chat-container">
    <aside class="chat-sidebar background">
        <h2>Chats</h2>
        <ul>
            <li onclick="openChat('Globalchat')">Globalchat</li>
            <li class="active-chat" onclick="openChat('Max Mustermann')">Max Mustermann</li>
            <li onclick="openChat('Maria Musterfrau')">Maria Musterfrau</li>
            <li onclick="openChat('Team DHBW')">Team DHBW</li>
        </ul>

        <section class="chat-sidebar-buttons">
            <section class="chat-sidebar-buttons">
                <button class="button-secondary" onclick="openAddContact()">Kontakt hinzufügen</button>
                <button class="button-secondary" onclick="openAddGroup()">Gruppe hinzufügen</button>

                <p class="margin-top-5 align-center">
                    <a href="legal_notice.php" class="font-secondary">Impressum</a>
                </p>
            </section>

        </section>
    </aside>

    <section class="chat-main">

        <div class="chat-nav-bar">
            <div style="display: flex; align-items: center;">
                <button class="chat-back-btn" onclick="closeChat()">
                    ← Zurück
                </button>
                <h2 id="currentChatName">Max Mustermann</h2>
            </div>
            <button class="chat-important-btn" onclick="toggleImportantPanel()">
                📌 Ablage
            </button>
        </div>

        <div class="chat-messages" id="chat-history">
            <!-- 1. Empfangen -->
            <div class="message received">
                <span class="timestamp">10:30</span>
                <div class="bubble">
                    Yo, bist du schon in der Uni?
                </div>
            </div>

            <!-- 2. Gesendet -->
            <div class="message sent">
                <span class="timestamp">10:32</span>
                <div class="bubble">
                    Ja, sitze gerade in der Bib. Versuche noch das Info-Skript von letzter Woche zu raffen 😅
                </div>
            </div>

            <!-- 3. Empfangen -->
            <div class="message received">
                <span class="timestamp">10:33</span>
                <div class="bubble">
                    Mein Beileid haha. Ich komm erst zur Vorlesung um 12.
                </div>
            </div>

            <!-- 4. Empfangen -->
            <div class="message received">
                <span class="timestamp">10:34</span>
                <div class="bubble">
                    Wollen wir davor noch was essen? Mensa?
                </div>
            </div>

            <!-- 5. Gesendet -->
            <div class="message sent">
                <span class="timestamp">10:36</span>
                <div class="bubble">
                    Klingt gut, hab Mega Hunger. Aber heute gibts glaub nur diesen seltsamen Eintopf... 🍲
                </div>
            </div>

            <!-- 6. Empfangen -->
            <div class="message received">
                <span class="timestamp">10:37</span>
                <div class="bubble">
                    Stimmt 💀 Lass lieber Döner holen gehen.
                </div>
            </div>

            <!-- 7. Gesendet -->
            <div class="message sent">
                <span class="timestamp">10:38</span>
                <div class="bubble">
                    Deal! Treffen uns dann 11:45 unten am Eingang?
                </div>
            </div>

            <!-- 8. Empfangen -->
            <div class="message received">
                <span class="timestamp">10:39</span>
                <div class="bubble">
                    Jo passt. Kannst du mir meinen Block mitbringen? Hab den gestern bei dir im Auto vergessen 🙈
                </div>
            </div>

            <!-- 9. Gesendet -->
            <div class="message sent">
                <span class="timestamp">10:40</span>
                <div class="bubble">
                    Klar, liegt schon im Rucksack. Das kostet dich aber eine Cola extra!
                </div>
            </div>

            <!-- 10. Empfangen -->
            <div class="message received">
                <span class="timestamp">10:41</span>
                <div class="bubble">
                    Träum weiter haha. Bis gleich!
                </div>
            </div>

            <!-- 11. Gesendet -->
            <div class="message sent">
                <span class="timestamp">11:15</span>
                <div class="bubble">
                    Sag mal, in welchem Raum sind wir nachher eigentlich? Wieder 204?
                </div>
            </div>

            <!-- 12. Empfangen -->
            <div class="message received">
                <span class="timestamp">11:16</span>
                <div class="bubble">
                    Ne, schau mal in den Plan. Wir müssen in den E01 wegen dem Gastvortrag.
                </div>
            </div>

            <!-- 13. Gesendet -->
            <div class="message sent">
                <span class="timestamp">11:17</span>
                <div class="bubble">
                    Ach stimmt, ganz vergessen. Danke, ich pack zusammen und komm runter.
                </div>
            </div>

            <form class="chat-input-container chat-input-floating" id="chatForm">
                <label for="chatmessage" class="visually-hidden">Nachricht eingeben</label>
                <textarea id="chatmessage" name="chatmessage" rows="2"
                          placeholder="Nachricht eingeben..."
                          inputmode="text" aria-label="Nachricht eingeben"></textarea>
                <button type="submit" class="style-bold">Senden</button>
            </form>
        </div>
        </section>
</main>

<div id="settingsModal" class="modal-overlay">
    <div class="modal-content popup-box">
        <button class="modal-close" onclick="closeSettings()">&times;</button>

        <section class="align-left margin-top-5">
            <h2 class="style-bold align-center margin-bottom-5">Einstellungen</h2>

            <section class="margin-bottom-3 settings-row">
                <label class="style-bold">Profilbild:</label>

                <div class="avatar-wrapper">
                    <img class="chat-avatar" id="avatar-preview" src="img/default-avatar.png" alt="Avatar Vorschaubild">
                    <input type="file" id="profile-picture" accept="image/*">
                </div>
            </section>

            <section class="margin-bottom-3 settings-row">
                <label class="style-bold">Username:</label>

                <div class="settings-field">
                    <span id="username-display">Luca Engl</span>
                    <button type="button" class="button-secondary settings-edit">Bearbeiten</button>
                </div>
            </section>

            <section class="margin-bottom-3 settings-row">
                <label class="style-bold">Email:</label>

                <div class="settings-field">
                    <span id="email-display">engl.tin25@student.dhbw-heidenheim.de</span>
                    <button type="button" class="button-secondary settings-edit">Bearbeiten</button>
                </div>
            </section>

            <section class="margin-bottom-3 settings-row">
                <label class="style-bold">Passwort:</label>

                <div class="settings-field">
                    <span id="password-display">••••••••</span>
                    <button type="button" class="button-secondary settings-edit">Bearbeiten</button>
                </div>
            </section>
        </section>
    </div>
</div>


<div id="addContactModal" class="modal-overlay">
    <div class="modal-content popup-box">
        <button class="modal-close" onclick="closeAddContact()">&times;</button>
        <h2>Kontakt hinzufügen</h2>
        <p><label for="contactEmail">DHBW E-Mail Adresse:</label></p>
        <input type="email" id="contactEmail" placeholder="max.mustermann@dhbw.de">
        <br><br>
        <button onclick="addContact()">Hinzufügen</button>
    </div>
</div>

<div id="addGroupModal" class="modal-overlay">
    <div class="modal-content popup-box">
        <button class="modal-close" onclick="closeAddGroup()">&times;</button>
        <h2>Gruppe erstellen</h2>
        <p><label for="groupName">Gruppenname:</label></p>
        <input type="text" id="groupName" placeholder="z.B. Team DHBW">
        <br><br>
        <p><label for="memberEmail">Mitglieder hinzufügen:</label></p>
        <div style="display: flex; gap: 10px; align-items: center;">
            <input type="email" id="memberEmail" placeholder="Email Adresse">
            <button class="button-secondary" onclick="addMemberToList()">+ Add</button>
        </div>
        <br>
        <div id="memberList" style="max-height: 100px; overflow-y: auto;"></div>
        <br>
        <button onclick="createGroup()">Erstellen</button>
    </div>
</div>


<!-- Ablage-->
    <aside id="importantPanel" class="important-panel">
        <div class="important-panel-header">
            <h3>📌 Wichtige Notizen</h3>
            <button class="panel-close-btn" onclick="toggleImportantPanel()">×</button>
        </div>

        <!-- Liste der Notizen -->
        <div id="notesList" class="notes-list">
            <p class="empty-state">Keine wichtigen Notizen für diesen Chat</p>
        </div>

        <!-- Input unten -->
        <div class="note-input-container">
            <textarea aria-label="Neue Notiz" id="newNoteInput" placeholder="Neue Notiz..." rows="1"></textarea>
            <button onclick="addNote()" class="style-bold">Senden</button>
        </div>
    </aside>

<script src="js/chat-page.js"></script>
</body>
</html>