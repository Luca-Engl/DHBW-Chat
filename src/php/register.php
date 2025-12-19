<?php
require_once __DIR__ . '/../components/db_connect.php';

/** @var PDO $pdo */

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $username = trim($_POST['username']);
    $email = trim($_POST['displayname']);
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_rep = isset($_POST['password_rep']) ? $_POST['password_rep'] : '';
    $faculty = !empty($_POST['faculty']) ? $_POST['faculty'] : null;
    $course = !empty($_POST['cursus']) ? $_POST['cursus'] : null;
    $year = !empty($_POST['year']) ? $_POST['year'] : null;

    if (empty($username) || empty($email) || empty($password) || empty($password_rep))
    {
        $error = "Bitte fülle alle Pflichtfelder aus!";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        $error = "Ungültige E-Mail-Adresse!";
    }
    elseif (!preg_match('/^[A-Za-z0-9]+$/', $username))
    {
        $error = "Benutzername darf nur Buchstaben und Zahlen enthalten!";
    }
    elseif (strlen($username) > 30)
    {
        $error = "Benutzername darf maximal 30 Zeichen haben!";
    }
    elseif ($password !== $password_rep)
    {
        $error = "Passwörter stimmen nicht überein!";
    }
    elseif (strlen($password) < 6)
    {
        $error = "Passwort muss mindestens 6 Zeichen lang sein!";
    }
    else
    {
        try
        {
            $stmt = $pdo->prepare("SELECT id FROM `user` WHERE username = ? OR email = ?");
            $stmt->execute(array($username, $email));

            if ($stmt->fetch())
            {
                $error = "Benutzername oder E-Mail bereits vergeben!";
            }
            else
            {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO `user` (username, email, password_hash, faculty, course, year) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute(array(
                        $username,
                        $email,
                        $password_hash,
                        $faculty,
                        $course,
                        $year
                ));

                $new_user_id = $pdo->lastInsertId();

                $stmt = $pdo->prepare("
                    SELECT id FROM chat WHERE chat_type = 'global' LIMIT 1
                ");
                $stmt->execute();
                $globalChat = $stmt->fetch();

                if ($globalChat)
                {
                    $stmt = $pdo->prepare("
                        INSERT INTO chat_participant (user_id, chat_id)
                        VALUES (?, ?)
                    ");
                    $stmt->execute(array($new_user_id, $globalChat['id']));
                }

                session_start();
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['username'] = $username;
                $_SESSION['loggedIn'] = true;
                header("Location: chat.php");
                exit();
            }
        }
        catch (PDOException $e)
        {
            error_log("Registration error: " . $e->getMessage());
            $error = "Ein Fehler ist aufgetreten. Bitte versuche es später erneut.";
        }
    }
}
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

        <?php if ($error): ?>
            <div class="error-message margin-bottom-3"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message margin-bottom-3"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php" id="registerForm">

            <div id="step1" class="form-step">
                <p>Benutzername:</p>
                <label>
                    <input type="text"
                           maxlength="30"
                           pattern="[A-Za-z0-9]+"
                           title="Nur Buchstaben und Zahlen erlaubt. Maximal 30 Zeichen."
                           inputmode="text"
                           id="username"
                           name="username"
                           placeholder=""
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           required>
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
                           value="<?php echo isset($_POST['displayname']) ? htmlspecialchars($_POST['displayname']) : ''; ?>"
                           required>
                </label>

                <div class="margin-bottom-5"></div>

                <p>Studiengang:</p>

                <label>
                    <select class="select-small" id="faculty" name="faculty" onchange="updateCourses()" required>
                        <option value="">Fakultät wählen ...</option>
                        <option value="T">Technik</option>
                        <option value="W">Wirtschaft</option>
                        <option value="S">Sozialwesen</option>
                        <option value="G">Gesundheit</option>
                        <option value="A">Sonstige</option>
                    </select>
                </label>

                <label>
                    <select class="select-small" id="cursus" name="cursus" onchange="updateYear()" disabled required>
                        <option value="">Erst Fakultät wählen ...</option>
                    </select>
                </label>

                <label>
                    <select class="select-small" id="year" name="year" disabled required>
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

            <div id="step2" class="form-step hidden">
                <p>Passwort eingeben:</p>
                <label>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder=""
                           autocomplete="new-password"
                           maxlength="30"
                           minlength="6">
                </label>

                <div class="margin-bottom-3"></div>

                <p>Passwort wiederholen:</p>
                <label>
                    <input type="password"
                           id="password_rep"
                           name="password_rep"
                           placeholder=""
                           autocomplete="new-password"
                           maxlength="30"
                           minlength="6">
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
</body>
</html>
