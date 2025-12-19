<?php

// test.php ist nur zu Testzwecken vorhanden und komplett KI-generiert

// Error Reporting aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DHBW Chat - Systemtest</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: #202025;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            color: #aaa;
        }

        .content {
            padding: 30px;
        }

        .test-section {
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
            padding-left: 20px;
        }

        .test-section h2 {
            color: #202025;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .warning {
            background: #fff3cd;
            color: #856404;
        }

        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 10px;
        }

        .info-box strong {
            color: #667eea;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 14px;
        }

        .icon {
            margin-right: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔧 DHBW Chat - Systemtest</h1>
        <p>Überprüfung der PHP-Konfiguration und Datenbankverbindung</p>
    </div>

    <div class="content">

        <!-- PHP Version Check -->
        <div class="test-section">
            <h2>📌 PHP-Konfiguration</h2>
            <?php
            $phpVersion = phpversion();
            $requiredVersion = '8.0.0';
            $versionOk = version_compare($phpVersion, $requiredVersion, '>=');
            ?>
            <span class="status <?php echo $versionOk ? 'success' : 'error'; ?>">
                    <?php echo $versionOk ? '✓' : '✗'; ?> PHP-Version: <?php echo $phpVersion; ?>
                </span>

            <div class="info-box">
                <strong>Erforderlich:</strong> PHP 8.0 oder höher<br>
                <strong>Installiert:</strong> PHP <?php echo $phpVersion; ?>
            </div>
        </div>

        <!-- PDO Check -->
        <div class="test-section">
            <h2>🔌 PDO MySQL Extension</h2>
            <?php
            $pdoAvailable = extension_loaded('pdo_mysql');
            ?>
            <span class="status <?php echo $pdoAvailable ? 'success' : 'error'; ?>">
                    <?php echo $pdoAvailable ? '✓' : '✗'; ?> PDO MySQL Extension
                </span>

            <?php if ($pdoAvailable): ?>
                <div class="info-box">
                    ✓ PDO MySQL Extension ist geladen und einsatzbereit
                </div>
            <?php else: ?>
                <div class="info-box">
                    ✗ PDO MySQL Extension nicht gefunden! Bitte in der php.ini aktivieren.
                </div>
            <?php endif; ?>
        </div>

        <!-- Database Connection Test -->
        <div class="test-section">
            <h2>🗄️ Datenbankverbindung</h2>
            <?php
            $dbConfig = [
                'host' => '127.0.0.1',
                'dbname' => 'web-eng_dhbw-chat',
                'username' => 'web-eng_dhbw-chat',
                'password' => 'chat'
            ];

            try {
                $pdo = new PDO(
                    "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4",
                    $dbConfig['username'],
                    $dbConfig['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );

                echo '<span class="status success">✓ Verbindung erfolgreich</span>';

                echo '<div class="info-box">';
                echo '<strong>Host:</strong> ' . htmlspecialchars($dbConfig['host']) . '<br>';
                echo '<strong>Datenbank:</strong> ' . htmlspecialchars($dbConfig['dbname']) . '<br>';
                echo '<strong>Benutzer:</strong> ' . htmlspecialchars($dbConfig['username']) . '<br>';
                echo '<strong>Charset:</strong> utf8mb4<br>';

                // MySQL Version
                $version = $pdo->query('SELECT VERSION()')->fetchColumn();
                echo '<strong>MySQL Version:</strong> ' . htmlspecialchars($version);
                echo '</div>';

            } catch (PDOException $e) {
                echo '<span class="status error">✗ Verbindung fehlgeschlagen</span>';
                echo '<div class="info-box">';
                echo '<strong>Fehler:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
                $pdo = null;
            }
            ?>
        </div>

        <?php if (isset($pdo) && $pdo !== null): ?>
            <!-- Table Structure Check -->
            <div class="test-section">
                <h2>📊 Tabellenstruktur</h2>
                <?php
                try {
                    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

                    if (count($tables) > 0) {
                        echo '<span class="status success">✓ ' . count($tables) . ' Tabelle(n) gefunden</span>';
                        echo '<table>';
                        echo '<thead><tr><th>Tabellenname</th><th>Anzahl Zeilen</th></tr></thead>';
                        echo '<tbody>';

                        foreach ($tables as $table) {
                            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($table) . '</td>';
                            echo '<td>' . number_format($count) . '</td>';
                            echo '</tr>';
                        }

                        echo '</tbody></table>';
                    } else {
                        echo '<span class="status warning">⚠ Keine Tabellen gefunden</span>';
                        echo '<div class="info-box">';
                        echo 'Bitte importiere die Datei <code>src/sql/schema.sql</code> über phpMyAdmin!';
                        echo '</div>';
                    }

                } catch (PDOException $e) {
                    echo '<span class="status error">✗ Fehler beim Abrufen der Tabellen</span>';
                    echo '<div class="info-box">';
                    echo '<strong>Fehler:</strong> ' . htmlspecialchars($e->getMessage());
                    echo '</div>';
                }
                ?>
            </div>

            <!-- Expected Tables Check -->
            <div class="test-section">
                <h2>✅ Erwartete Tabellen</h2>
                <?php
                $expectedTables = [
                    'users' => 'Benutzerverwaltung',
                    'chats' => 'Chat-Räume',
                    'messages' => 'Nachrichten',
                    'user_chats' => 'Benutzer-Chat-Zuordnung'
                ];

                $allPresent = true;
                echo '<table>';
                echo '<thead><tr><th>Tabelle</th><th>Beschreibung</th><th>Status</th></tr></thead>';
                echo '<tbody>';

                foreach ($expectedTables as $tableName => $description) {
                    $exists = in_array($tableName, $tables);
                    $allPresent = $allPresent && $exists;

                    echo '<tr>';
                    echo '<td><code>' . htmlspecialchars($tableName) . '</code></td>';
                    echo '<td>' . htmlspecialchars($description) . '</td>';
                    echo '<td>';
                    if ($exists) {
                        echo '<span class="status success" style="font-size: 12px; padding: 3px 10px;">✓ OK</span>';
                    } else {
                        echo '<span class="status error" style="font-size: 12px; padding: 3px 10px;">✗ Fehlt</span>';
                    }
                    echo '</td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';

                if (!$allPresent) {
                    echo '<div class="info-box" style="margin-top: 15px; background: #fff3cd; border-color: #ffc107;">';
                    echo '<strong>⚠️ Hinweis:</strong> Nicht alle erwarteten Tabellen wurden gefunden. Bitte stelle sicher, dass die schema.sql korrekt importiert wurde.';
                    echo '</div>';
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- PHP Extensions -->
        <div class="test-section">
            <h2>🔧 PHP Extensions</h2>
            <?php
            $requiredExtensions = [
                'pdo' => 'PHP Data Objects',
                'pdo_mysql' => 'PDO MySQL Driver',
                'session' => 'Session Support',
                'json' => 'JSON Support',
                'mbstring' => 'Multibyte String'
            ];

            echo '<table>';
            echo '<thead><tr><th>Extension</th><th>Beschreibung</th><th>Status</th></tr></thead>';
            echo '<tbody>';

            foreach ($requiredExtensions as $ext => $desc) {
                $loaded = extension_loaded($ext);
                echo '<tr>';
                echo '<td><code>' . htmlspecialchars($ext) . '</code></td>';
                echo '<td>' . htmlspecialchars($desc) . '</td>';
                echo '<td>';
                if ($loaded) {
                    echo '<span class="status success" style="font-size: 12px; padding: 3px 10px;">✓ Geladen</span>';
                } else {
                    echo '<span class="status error" style="font-size: 12px; padding: 3px 10px;">✗ Fehlt</span>';
                }
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
            ?>
        </div>

        <!-- Summary -->
        <div class="test-section">
            <h2>📋 Zusammenfassung</h2>
            <?php
            $allChecks = [
                'PHP Version' => $versionOk,
                'PDO MySQL' => $pdoAvailable,
                'Datenbankverbindung' => isset($pdo) && $pdo !== null,
                'Tabellen vorhanden' => isset($tables) && count($tables) > 0
            ];

            $passedChecks = array_filter($allChecks);
            $totalChecks = count($allChecks);
            $passed = count($passedChecks);

            if ($passed === $totalChecks) {
                echo '<div class="info-box" style="background: #d4edda; border-color: #28a745;">';
                echo '<strong style="color: #155724;">🎉 Alle Tests erfolgreich!</strong><br>';
                echo 'Dein DHBW Chat System ist korrekt konfiguriert und einsatzbereit.';
                echo '</div>';
            } else {
                echo '<div class="info-box" style="background: #fff3cd; border-color: #ffc107;">';
                echo '<strong style="color: #856404;">⚠️ ' . $passed . ' von ' . $totalChecks . ' Tests bestanden</strong><br>';
                echo 'Bitte behebe die oben aufgeführten Probleme.';
                echo '</div>';
            }
            ?>
        </div>

    </div>

    <div class="footer">
        <strong>⚠️ SICHERHEITSHINWEIS:</strong> Diese Datei enthält Systeminformationen und sollte vor dem Produktivbetrieb gelöscht werden!<br>
        DHBW Chat - Entwickelt von Studierenden für Studierende
    </div>
</div>
</body>
</html>