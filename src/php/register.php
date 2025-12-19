<?php
require_once __DIR__ . '/../components/db_connect.php';

/** @var PDO $pdo */

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['ajax_check'] ?? '') === '1')) {
    header('Content-Type: application/json; charset=utf-8');

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['displayname'] ?? '');

    if ($username === '' || $email === '') {
        echo json_encode(['ok' => false, 'error' => 'Bitte fülle Benutzername und E-Mail aus.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok' => false, 'error' => 'Ungültige E-Mail-Adresse eingegeben.']);
        exit;
    }
    if (!preg_match('/^[A-Za-z0-9]+$/', $username)) {
        echo json_encode(['ok' => false, 'error' => 'Der Benutzername darf nur Buchstaben und Zahlen enthalten.']);
        exit;
    }
    if (strlen($username) > 30) {
        echo json_encode(['ok' => false, 'error' => 'Der Benutzername darf maximal 30 Zeichen haben.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM `user` WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            echo json_encode(['ok' => false, 'error' => 'Der Benutzername oder diese E-Mail ist bereits vergeben.']);
            exit;
        }

        echo json_encode(['ok' => true]);
        exit;
    } catch (PDOException $e) {
        error_log("Registration availability check error: " . $e->getMessage());
        echo json_encode(['ok' => false, 'error' => 'Ein Fehler ist aufgetreten. Bitte versuche es später erneut.']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['displayname'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_rep = $_POST['password_rep'] ?? '';
    $faculty = !empty($_POST['faculty']) ? $_POST['faculty'] : null;
    $course = !empty($_POST['cursus']) ? $_POST['cursus'] : null;
    $year = !empty($_POST['year']) ? $_POST['year'] : null;

    if ($username === '' || $email === '' || $password === '' || $password_rep === '') {
        $error = "Bitte fülle alle Pflichtfelder aus.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Die E-Mail Adresse ist ungültig.";
    } elseif (!preg_match('/^[A-Za-z0-9]+$/', $username)) {
        $error = "Der Benutzername darf nur Buchstaben und Zahlen enthalten.";
    } elseif (strlen($username) > 30) {
        $error = "Der Benutzername darf maximal 30 Zeichen haben.";
    } elseif ($faculty === null || $course === null || $year === null) {
        $error = "Bitte wähle eine Fakultät, einen Studiengang und ein Jahr aus.";
    } elseif ($password !== $password_rep) {
        $error = "Die Passwörter stimmen nicht überein.";
    } elseif (strlen($password) < 6) {
        $error = "Das Passwort muss mindestens 6 Zeichen lang sein.";
    } elseif (strlen($password) > 30) {
        $error = "Das Passwort darf maximal 30 Zeichen haben.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM `user` WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                $error = "Der Benutzername oder diese E-Mail ist bereits vergeben.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO `user` (username, email, password_hash, faculty, course, year) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                        $username,
                        $email,
                        $password_hash,
                        $faculty,
                        $course,
                        $year
                ]);

                $new_user_id = $pdo->lastInsertId();

                $stmt = $pdo->prepare("SELECT id FROM chat WHERE chat_type = 'global' LIMIT 1");
                $stmt->execute();
                $globalChat = $stmt->fetch();

                if ($globalChat) {
                    $stmt = $pdo->prepare("
                        INSERT INTO chat_participant (user_id, chat_id)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$new_user_id, $globalChat['id']]);
                }

                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['username'] = $username;
                $_SESSION['loggedIn'] = true;

                header("Location: chat.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = "Ein Fehler ist aufgetreten. Bitte versuche es später erneut.";
        }
    }
}

$startStep2 = ($_SERVER['REQUEST_METHOD'] === 'POST' && ($error !== '') && (($_POST['password'] ?? '') !== '' || ($_POST['password_rep'] ?? '') !== ''));
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/font.css" />
    <link rel="stylesheet" href="../css/style.css"/>
    <link rel="stylesheet" href="../css/layout.css"/>
    <title>DHBW Chat - Registrierung</title>
    <link rel="icon" type="image/png" href="../img/favicon.png">

</head>
<body>
<main class="img-background-login center-box" id="top">
    <a href="./index.php">
        <img src="../img/DHBW-Banner-Chat-Red.png" class="img-logo-login" alt="DHBW-Chat-Logo">
    </a>

    <section class="popup-box">
        <h2>Registrieren</h2>
        <div class="margin-bottom-3"></div>

        <div id="pageError"
             class="error-message margin-bottom-3<?php echo ($error === '' ? ' is-hidden' : ''); ?>"
             role="alert"
             aria-live="polite">
            <span id="pageErrorText"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <?php if ($success): ?>
            <div class="success-message margin-bottom-3"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php" id="registerForm" novalidate>

            <div id="step1" class="form-step<?php echo ($startStep2 ? ' hidden' : ''); ?>">
                <p>Benutzername:</p>
                <label>
                    <input type="text"
                           maxlength="30"
                           inputmode="text"
                           id="username"
                           name="username"
                           placeholder=""
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                </label>

                <div class="margin-bottom-3"></div>

                <p>DHBW E-Mail Adresse:</p>
                <label>
                    <input type="email"
                           inputmode="email"
                           maxlength="30"
                           id="displayname"
                           name="displayname"
                           placeholder=""
                           value="<?php echo isset($_POST['displayname']) ? htmlspecialchars($_POST['displayname'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                </label>

                <div class="margin-bottom-5"></div>

                <p>Studiengang:</p>

                <label>
                    <select class="select-small" id="faculty" name="faculty" onchange="updateCourses()">
                        <option value="">Fakultät wählen ...</option>
                        <option value="T">Technik</option>
                        <option value="W">Wirtschaft</option>
                        <option value="S">Sozialwesen</option>
                        <option value="G">Gesundheit</option>
                        <option value="A">Sonstige</option>
                    </select>
                </label>

                <label>
                    <select class="select-small" id="cursus" name="cursus" onchange="updateYear()" disabled>
                        <option value="">Erst Fakultät wählen ...</option>
                    </select>
                </label>

                <label>
                    <select class="select-small" id="year" name="year" disabled>
                        <option value="">Erst Studiengang wählen ...</option>
                    </select>
                </label>

                <div class="margin-bottom-5"></div>

                <button type="button" id="nextBtn" onclick="showStep2()">Weiter</button>

                <div class="margin-bottom-5"></div>

                <h3>oder</h3>

                <div class="margin-bottom-5"></div>

                <a href="login.php">
                    <button class="button-secondary" type="button">Zurück zur Anmeldung</button>
                </a>
            </div>

            <div id="step2" class="form-step<?php echo ($startStep2 ? '' : ' hidden'); ?>">
                <p>Passwort eingeben:</p>
                <label>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder=""
                           autocomplete="new-password"
                           maxlength="30">
                </label>

                <div class="margin-bottom-3"></div>

                <p>Passwort wiederholen:</p>
                <label>
                    <input type="password"
                           id="password_rep"
                           name="password_rep"
                           placeholder=""
                           autocomplete="new-password"
                           maxlength="30">
                </label>

                <div class="margin-bottom-5"></div>

                <button type="submit">Registrieren</button>

                <div class="margin-bottom-5"></div>

                <h3>oder</h3>

                <div class="margin-bottom-5"></div>

                <button class="button-secondary" type="button" onclick="showStep1()">Zurück</button>
            </div>

        </form>
    </section>
</main>

<footer class="footer-grid">
    <section class="footer-left"></section>
    <section class="footer-center">
        <a class="img-logo-footer" href="#top">
            <img src="../img/DHBW-Banner-Chat-Red.png" alt="DHBW-Chat logo" class="img-logo-footer">
        </a>
        <a href="legal_notice.php">Impressum</a>
        <a href="help.php">Hilfe</a>
        <a href="privacy.php">Datenschutz</a>
    </section>
</footer>

<script src="../js/register.js"></script>
<script>
    (function () {
        const box = document.getElementById('pageError');
        const text = document.getElementById('pageErrorText');

        let timer = null;

        function hideError() {
            if (!box) return;
            box.classList.add('is-hidden');
        }

        function showError(message) {
            if (!box || !text) return;
            text.textContent = message;
            box.classList.remove('is-hidden');

            if (timer) window.clearTimeout(timer);
            timer = window.setTimeout(hideError, 5000);
        }

        if (text && text.textContent.trim() !== '') {
            timer = window.setTimeout(hideError, 5000);
        }

        const postFaculty = <?php echo json_encode($_POST['faculty'] ?? ''); ?>;
        const postCursus  = <?php echo json_encode($_POST['cursus'] ?? ''); ?>;
        const postYear    = <?php echo json_encode($_POST['year'] ?? ''); ?>;

        window.addEventListener('DOMContentLoaded', function () {
            const facultyEl = document.getElementById('faculty');
            const cursusEl  = document.getElementById('cursus');
            const yearEl    = document.getElementById('year');

            if (facultyEl && postFaculty) {
                facultyEl.value = postFaculty;
                if (typeof updateCourses === 'function') updateCourses();
            }

            window.setTimeout(function () {
                if (cursusEl && postCursus) {
                    cursusEl.value = postCursus;
                    if (typeof updateYear === 'function') updateYear();
                }
                window.setTimeout(function () {
                    if (yearEl && postYear) yearEl.value = postYear;
                }, 50);
            }, 50);
        });

        function validateStep1Basic() {
            const username = (document.getElementById('username')?.value || '').trim();
            const email = (document.getElementById('displayname')?.value || '').trim();
            const faculty = (document.getElementById('faculty')?.value || '').trim();
            const cursus = (document.getElementById('cursus')?.value || '').trim();
            const year = (document.getElementById('year')?.value || '').trim();

            if (username === '' || email === '') {
                showError('Bitte fülle Benutzername und E-Mail aus!');
                return false;
            }
            if (!/^[A-Za-z0-9]+$/.test(username)) {
                showError('Benutzername darf nur Buchstaben und Zahlen enthalten!');
                return false;
            }
            if (username.length > 30) {
                showError('Benutzername darf maximal 30 Zeichen haben!');
                return false;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('Ungültige E-Mail-Adresse!');
                return false;
            }
            if (faculty === '' || cursus === '' || year === '') {
                showError('Bitte wähle Fakultät, Studiengang und Jahr aus!');
                return false;
            }

            hideError();
            return true;
        }

        function validateStep2Basic() {
            const password = document.getElementById('password')?.value || '';
            const passwordRep = document.getElementById('password_rep')?.value || '';

            if (password === '' || passwordRep === '') {
                showError('Bitte fülle alle Pflichtfelder aus.');
                return false;
            }
            if (password.length < 6) {
                showError('Das Passwort muss mindestens 6 Zeichen lang sein.');
                return false;
            }
            if (password.length > 30) {
                showError('Das Passwort darf maximal 30 Zeichen haben.');
                return false;
            }
            if (password !== passwordRep) {
                showError('Die Passwörter stimmen nicht überein.');
                return false;
            }

            hideError();
            return true;
        }

        async function checkAvailability(username, email) {
            const body = new URLSearchParams();
            body.set('ajax_check', '1');
            body.set('username', username);
            body.set('displayname', email);

            const res = await fetch('register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString(),
                credentials: 'same-origin'
            });

            const data = await res.json().catch(() => null);
            if (!data) {
                return { ok: false, error: 'Ein Fehler ist aufgetreten.' };
            }
            return data;
        }

        window.showStep2 = async function () {
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const nextBtn = document.getElementById('nextBtn');

            if (!validateStep1Basic()) return;

            const username = (document.getElementById('username')?.value || '').trim();
            const email = (document.getElementById('displayname')?.value || '').trim();

            try {
                if (nextBtn) nextBtn.disabled = true;

                const result = await checkAvailability(username, email);
                if (!result.ok) {
                    showError(result.error || 'Dieser Benutzername oder diese E-Mail bereits vergeben.');
                    return;
                }

                if (step1) step1.classList.add('hidden');
                if (step2) step2.classList.remove('hidden');

                document.getElementById('password')?.focus();
            } catch (e) {
                showError('Ein Fehler ist aufgetreten. Bitte versuche es später erneut.');
            } finally {
                if (nextBtn) nextBtn.disabled = false;
            }
        };

        window.showStep1 = function () {
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');

            hideError();

            if (step2) step2.classList.add('hidden');
            if (step1) step1.classList.remove('hidden');

            document.getElementById('username')?.focus();
        };

        const form = document.getElementById('registerForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!validateStep1Basic() || !validateStep2Basic()) {
                    e.preventDefault();
                }
            });
        }
    })();
</script>
</body>
</html>
