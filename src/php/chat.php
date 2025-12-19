<?php
require_once __DIR__ . '/../components/db_connect.php';

/** @var PDO $pdo */

if (session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

$guestChatId = null;
$guestChatName = null;

if (! empty($_GET['groupcode']))
{
    $groupcode = preg_replace('/[^A-Za-z0-9]/', '', $_GET['groupcode']);

    if ($groupcode === '')
    {
        header('Location: login.php');
        exit;
    }

    try {
        // Schritt 1: Gruppe finden
        $stmt = $pdo->prepare("
            SELECT id, chat_name 
            FROM chat 
            WHERE invite_code = ? AND chat_type = 'group'
        ");
        $stmt->execute([strtoupper($groupcode)]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$group) {
            header('Location: login.php?error=invalid_code');
            exit;
        }

        // Schritt 2: Gast-User erstellen oder finden
        $guestUsername = 'Guest_' . session_id();
        $guestEmail = 'guest_' . session_id() . '@temp.local';

        // Prüfen ob Gast-User bereits existiert
        $stmt = $pdo->prepare("SELECT id FROM `user` WHERE username = ?");
        $stmt->execute([$guestUsername]);
        $existingGuest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingGuest) {
            $guestUserId = $existingGuest['id'];
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO `user` (username, email, password_hash, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $dummyPassword = bin2hex(random_bytes(32));
            $dummyHash = password_hash($dummyPassword, PASSWORD_DEFAULT);
            $stmt->execute([$guestUsername, $guestEmail, $dummyHash]);

            $guestUserId = $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare("
            INSERT IGNORE INTO chat_participant (user_id, chat_id, joined_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$guestUserId, $group['id']]);

        $_SESSION['isGuest'] = true;
        $_SESSION['user_id'] = $guestUserId;
        $_SESSION['groupcode'] = $groupcode;
        $_SESSION['guest_chat_id'] = $group['id'];
        $_SESSION['guest_chat_name'] = $group['chat_name'];
        $_SESSION['username'] = $guestUsername;

        $guestChatId = $group['id'];
        $guestChatName = $group['chat_name'];

    } catch (PDOException $e) {
        error_log("Error joining group: " . $e->getMessage());
        header('Location: login. php? error=db_error');
        exit;
    }
}

if (! empty($_SESSION['isGuest']) && ! empty($_SESSION['guest_chat_id']))
{
    $guestChatId = $_SESSION['guest_chat_id'];
    $guestChatName = $_SESSION['guest_chat_name'] ?? 'Gruppenchat';
}

$loggedIn = ! empty($_SESSION['loggedIn']);
$isGuest  = !empty($_SESSION['isGuest']);

if (! $loggedIn && !$isGuest)
{
    header('Location: login.php');
    exit;
}

$currentUser  = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unbekannt';
$currentEmail = 'E-Mail nicht verfügbar';

if ($loggedIn && ! $isGuest && isset($_SESSION['user_id']))
{
    try
    {
        $stmt = $pdo->prepare("SELECT email FROM `user` WHERE id = ?");
        $stmt->execute(array($_SESSION['user_id']));
        $userData = $stmt->fetch();

        if ($userData)
        {
            $currentEmail = $userData['email'];
        }

        $stmt = $pdo->prepare("
            SELECT id FROM chat WHERE chat_type = 'global' LIMIT 1
        ");
        $stmt->execute();
        $globalChat = $stmt->fetch();

        if ($globalChat)
        {
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO chat_participant (user_id, chat_id)
                VALUES (?, ?)
            ");
            $stmt->execute(array($_SESSION['user_id'], $globalChat['id']));
        }
    }
    catch (PDOException $e)
    {
        error_log("Error fetching user data: " .  $e->getMessage());
    }
}

$currentGroup = isset($_SESSION['groupcode']) ? $_SESSION['groupcode'] : null;
?>
<!DOCTYPE html>
<html lang="de" class="chat-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/font.css" />
    <link rel="stylesheet" href="../css/style.css"/>
    <link rel="stylesheet" href="../css/layout.css"/>
    <title>DHBW Chat - Chatbereich</title>
    <link rel="icon" type="image/png" href="../img/favicon.png">
</head>
<body class="chat-page">
<header class="chat-page-header">
    <nav class="chat-nav-grid margin-right-1">
        <section class="chat-nav-left">
            <a href="./index.php">
                <img src="../img/DHBW-Banner-Chat-Red.png" class="img-logo-nav" alt="DHBW-Chat-Logo">
            </a>
        </section>
        <section class="chat-nav-center"></section>
        <section class="nav-right">
            <?php if ($loggedIn): ?>
                <a href="logout.php" class="font-secondary nav-logout margin-right-5">
                    🚪 Abmelden
                </a>
            <?php elseif ($isGuest): ?>
                <a href="logout.php" class="font-secondary nav-logout margin-right-5">
                    🚪 Verlassen
                </a>
            <?php endif; ?>

            <?php if (! $isGuest): ?>
                <a href="#" class="nav-username margin-right-5 style-bold" onclick="openSettings(); return false;">
                    <?php echo htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8'); ?>
                </a>
                <a href="#" onclick="openSettings(); return false;" class="nav-settings">
                    <img src="../img/default-avatar.png" alt="User-Avatar">
                </a>
            <?php else: ?>
                <span class="nav-username margin-right-5 style-bold">
                    Gastaccount
                </span>
                <span class="nav-settings">
                    <img src="../img/default-avatar.png" alt="User-Avatar">
                </span>
            <?php endif; ?>
        </section>
    </nav>
</header>

<main class="chat-container">
    <aside class="chat-sidebar background">
        <h2>Chats</h2>
        <ul id="chatList">
            <?php if ($isGuest && $guestChatName): ?>
                <li class="chat-item active" data-chat-id="<?php echo $guestChatId; ?>" data-chat-type="group">
                    <span class="chat-name"><?php echo htmlspecialchars($guestChatName); ?></span>
                </li>
            <?php elseif (!$isGuest): ?>
                <li>Lädt Chats...</li>
            <?php endif; ?>
        </ul>

        <?php if (! $isGuest): ?>
            <section class="chat-sidebar-buttons">
                <button class="button-secondary" onclick="openAddContact()">Kontakt hinzufügen</button>
                <button class="button-secondary" onclick="openAddGroup()">Gruppe erstellen</button>

                <p class="margin-top-5 align-center">
                    <a href="legal_notice.php" class="font-secondary">Impressum</a>
                </p>
            </section>
        <?php else: ?>
            <section class="chat-sidebar-buttons">
                <p class="margin-top-5 align-center" style="color: #888; font-size: 0.9rem;">
                    👤 Du bist als Gast beigetreten
                </p>
                <p class="margin-top-3 align-center">
                    <a href="legal_notice.php" class="font-secondary">Impressum</a>
                </p>
            </section>
        <?php endif; ?>
    </aside>

    <section class="chat-main">
        <section class="chat-nav-bar">
            <section style="display: flex; align-items: center; gap: 10px;">
                <button class="chat-back-btn" onclick="closeChat()">
                    ← Zurück
                </button>
                <h2 id="currentChatName"><?php echo $isGuest && $guestChatName ? htmlspecialchars($guestChatName) : 'Wähle einen Chat'; ?></h2>
                <button id="manageGroupBtn" class="chat-manage-btn" onclick="openManageGroupFromNav()" style="display: none;" title="Gruppe verwalten">
                    ⚙️ Verwalten
                </button>
            </section>
            <button class="chat-important-btn" onclick="toggleImportantPanel()">
                📌 Wichtige Notizen
            </button>
        </section>

        <section class="chat-messages" id="chat-history">
            <p style="text-align: center; color: #888; padding: 20px;">
                <?php echo $isGuest ? 'Lade Nachrichten...' : 'Wähle einen Chat aus der Liste'; ?>
            </p>

            <form class="chat-input-container chat-input-floating" id="chatForm">
                <label for="chatmessage" class="visually-hidden">Nachricht eingeben</label>
                <textarea id="chatmessage" maxlength="2048" name="chatmessage" rows="1"
                          placeholder="Neue Nachricht eingeben ..."
                          inputmode="text" aria-label="Nachricht eingeben"></textarea>
                <button type="submit" class="style-bold">Senden  ➡ </button>
            </form>
        </section>
    </section>
</main>

<?php if (! $isGuest): ?>
    <section id="settingsModal" class="modal-overlay">
        <section class="modal-content popup-box">
            <button class="modal-close" onclick="closeSettings()">&times;</button>

            <section class="align-left margin-top-5">
                <h2 class="style-bold align-center margin-bottom-5">Profileinstellungen</h2>

                <section class="margin-bottom-3 settings-row">
                    <label class="style-bold">Profilbild:</label>
                    <section class="avatar-wrapper">
                        <img class="chat-avatar" id="avatar-preview" src="../img/default-avatar.png" alt="Avatar Vorschaubild">
                        <input type="file" id="profile-picture" accept="image/*">
                    </section>
                </section>

                <section class="margin-bottom-3 settings-row">
                    <label class="style-bold">Benutzername:</label>
                    <section class="settings-field">
                        <span id="username-display"><?php echo htmlspecialchars($currentUser); ?></span>
                        <button type="button" class="button-secondary settings-edit" onclick="openEditUsername()">✏️ Bearbeiten</button>
                    </section>
                </section>

                <section class="margin-bottom-3 settings-row">
                    <label class="style-bold">E-Mail-Adresse:</label>
                    <section class="settings-field">
                        <span id="email-display"><?php echo htmlspecialchars($currentEmail); ?></span>
                        <button type="button" class="button-secondary settings-edit" onclick="openEditEmail()">✏️ Bearbeiten</button>
                    </section>
                </section>

                <section class="margin-bottom-3 settings-row">
                    <label class="style-bold">Passwort:</label>
                    <section class="settings-field">
                        <span id="password-display">••••••••</span>
                        <button type="button" class="button-secondary settings-edit" onclick="openEditPassword()">✏️ Bearbeiten</button>
                    </section>
                </section>
            </section>
        </section>
    </section>

    <section id="editUsernameModal" class="modal-overlay">
        <section class="modal-content popup-box">
            <button class="modal-close" onclick="closeEditUsername()">&times;</button>
            <h2>Benutzername ändern</h2>
            <br>
            <div id="username-error" class="error-message hidden"></div>
            <div id="username-success" class="success-message hidden"></div>
            <p><label for="newUsername">Neuen Benutzernamen eingeben:</label></p>
            <input type="text" id="newUsername" maxlength="30" pattern="[A-Za-z0-9]+" placeholder="">
            <br><br>
            <button onclick="updateUsername()">✔ Speichern</button>
        </section>
    </section>

    <section id="editEmailModal" class="modal-overlay">
        <section class="modal-content popup-box">
            <button class="modal-close" onclick="closeEditEmail()">&times;</button>
            <h2>E-Mail-Adresse ändern</h2>
            <br>
            <div id="email-error" class="error-message hidden"></div>
            <div id="email-success" class="success-message hidden"></div>
            <p><label for="newEmail">Neue E-Mail-Adresse eingeben:</label></p>
            <input type="email" maxlength="30" id="newEmail" placeholder="">
            <br><br>
            <button onclick="updateEmail()">✔ Speichern</button>
        </section>
    </section>

    <section id="editPasswordModal" class="modal-overlay">
        <section class="modal-content popup-box">
            <button class="modal-close" onclick="closeEditPassword()">&times;</button>
            <h2>Passwort bearbeiten:</h2>
            <br>
            <div id="password-error" class="error-message hidden"></div>
            <div id="password-success" class="success-message hidden"></div>
            <p><label for="oldPassword">Altes Passwort eingeben:</label></p>
            <input type="password" id="oldPassword" minlength="6" maxlength="30" placeholder="">
            <br>
            <p><label for="newPassword">Neues Passwort eingeben:</label></p>
            <input type="password" id="newPassword" minlength="6" maxlength="30" placeholder="">
            <br>
            <p><label for="newPasswordConfirm">Neues Passwort wiederholen:</label></p>
            <input type="password" id="newPasswordConfirm" minlength="6" maxlength="30" placeholder="">
            <br><br>
            <button onclick="updatePassword()">✔ Speichern</button>
        </section>
    </section>

    <section id="addContactModal" class="modal-overlay">
        <section class="modal-content popup-box">
            <button class="modal-close" onclick="closeAddContact()">&times;</button>
            <h2>Neuen Kontakt hinzufügen:</h2>
            <br>

            <div id="contact-error" class="error-message hidden"></div>
            <div id="contact-success" class="success-message hidden"></div>

            <p><label for="contactInput">Benutzername oder E-Mail-Adresse eingeben:</label></p>
            <input type="text" id="contactInput" maxlength="30" placeholder="">
            <br><br>
            <button onclick="addContact()">✔ Kontakt hinzufügen</button>
        </section>
    </section>

    <section id="addGroupModal" class="modal-overlay">
        <section class="modal-content popup-box">
            <button class="modal-close" onclick="closeAddGroup()">&times;</button>
            <h2>Neue Gruppe erstellen:</h2>
            <br>

            <div id="group-error" class="error-message hidden"></div>
            <div id="group-success" class="success-message hidden"></div>

            <p><label for="groupName">Gruppenname eingeben:</label></p>
            <input type="text" id="groupName" maxlength="15" placeholder="">
            <br><br>
            <p><label for="memberInput">Mitglieder hinzufügen:</label></p>
            <section style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="memberInput" maxlength="30" placeholder="" style="flex: 1;">
                <button class="button-secondary" onclick="addMemberToList()">➕ Hinzufügen</button>
            </section>
            <br>
            <section id="memberList" style="max-height: 150px; overflow-y: auto;"></section>
            <br>
            <p class="font-error style-italic">Achtung: Alle hinzugefügten Mitglieder können die Gruppe und Mitglieder verwalten.</p>
            <br>
            <button onclick="createGroup()">✔ Gruppe erstellen</button>
        </section>
    </section>

    <section id="manageGroupModal" class="modal-overlay">
        <section class="modal-content popup-box">
            <button class="modal-close" onclick="closeManageGroup()">&times;</button>
            <h2 id="manageGroupTitle">Gruppe verwalten</h2>

            <div id="manage-group-error" class="error-message hidden"></div>
            <div id="manage-group-success" class="success-message hidden"></div>

            <h3 style="margin-top: 20px; margin-bottom: 10px;">Gruppenname bearbeiten:</h3>
            <section style="display: flex; gap: 10px; align-items: center; margin-bottom: 20px;">
                <input type="text" id="manageGroupNameInput" maxlength="15" placeholder="" style="flex: 1;">
                <button class="button-secondary" type="button" onclick="updateGroupName()">✏️ Gruppenname aktualisieren</button>
            </section>

            <h3 style="margin-top: 20px; margin-bottom: 10px;">Mitglied hinzufügen:</h3>
            <section style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="addMemberInput" maxlength="30" placeholder="" style="flex: 1;">
                <button class="button-secondary" onclick="addGroupMember()">➕ Hinzufügen</button>
            </section>

            <section class="settings-row">
                <h3 style="margin-top: 20px; margin-bottom: 10px;">Gast hinzufügen:</h3>
                <p class="font-secondary" style="font-size: 0.9rem;">
                </p>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input
                            type="text"
                            id="inviteCodeDisplay"
                            readonly
                            value="Lade Code..."
                            style="text-align: center; font-size: 1.5rem; letter-spacing: 0.3rem; font-weight: bold; font-family: 'Courier New', monospace;"
                    >
                    <button
                            type="button"
                            class="button-secondary"
                            onclick="copyInviteCodeToClipboard()"
                            id="copyInviteCodeBtn"
                    >📋 Code kopieren
                    </button>
                </div>
            </section>

            <h3 style="margin-top: 20px; margin-bottom: 10px;">Mitgliederliste:</h3>
            <section id="currentMembersList" style="max-height: 200px; overflow-y: auto; margin-bottom: 20px;">
                <p style="color: #888;">Lade Mitglieder...</p>
            </section>

        </section>
    </section>

    <section id="editMessageModal" class="modal-overlay">
        <section class="modal-content popup-box">
            <button class="modal-close" onclick="closeEditMessage()">&times;</button>
            <h2>Nachricht bearbeiten</h2>
            <br>
            <div id="edit-error" class="error-message hidden"></div>
            <div id="edit-success" class="success-message hidden"></div>

            <p><label for="editMessageText">Neue Nachricht eingeben:</label></p>
            <textarea id="editMessageText" rows="2" maxlength="2048" placeholder=""></textarea>
            <br><br>

            <section>
                <button onclick="saveEditedMessage()" class="style-bold">✔ Speichern</button>
                <button onclick="closeEditMessage()" class="button-secondary">✖ Abbrechen</button>
            </section>
        </section>
    </section>

    <div id="deleteMessageModal" class="modal-overlay">
        <div class="modal-content popup-box">
            <button class="modal-close" onclick="closeDeleteMessage()">&times;</button>
            <h2>Nachricht löschen</h2>
            <p>Möchtest du diese Nachricht wirklich unwiderruflich löschen?</p>
            <div id="delete-error" class="error-message hidden"></div>
            <div class="button-container">
                <button class="button-primary" onclick="confirmDeleteMessage()">✔ Löschen</button>
                <button class="button-secondary" onclick="closeDeleteMessage()">✖ Abbrechen</button>
            </div>
        </div>
    </div>
<?php endif; ?>

<aside id="importantPanel" class="important-panel">
    <section class="important-panel-header">
        <h3>📌 Wichtige Notizen</h3>
        <button class="panel-close-btn" onclick="toggleImportantPanel()">×</button>
    </section>
    <section id="notesList" class="notes-list">
        <p class="empty-state">Keine wichtigen Notizen für diesen Chat. Notiere dir etwas! :)</p>
    </section>
    <section class="note-input-container">
        <textarea aria-label="Neue Notiz" maxlength="1024" id="newNoteInput" placeholder="Neue Notiz hinzufügen ..." rows="1"></textarea>
        <button onclick="addNote()" class="style-bold">Senden  ➡ </button>
    </section>
</aside>

<script>
    var isGuest = <?php echo $isGuest ? 'true' : 'false'; ?>;
    var guestChatId = <?php echo $guestChatId ? $guestChatId : 'null'; ?>;
    var guestChatName = <?php echo $guestChatName ? '"' . addslashes($guestChatName) . '"' : 'null'; ?>;
</script>

<script src="../js/chat-page.js"></script>
</body>
</html>