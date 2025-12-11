# DHBW Chat

Eine sichere Chat-Anwendung für Studierende der Dualen Hochschule Baden-Württemberg (DHBW), entwickelt von Studierenden für Studierende.

## Überblick

DHBW Chat ist eine webbasierte Kommunikationsplattform mit Fokus auf Datenschutz und Benutzerfreundlichkeit. Die Anwendung ermöglicht Direktnachrichten, Gruppenchats und einen globalen Chat.

### Hauptmerkmale

- **Globaler Chat** – Campus-weiter Austausch über alle Standorte
- **Gruppenchats** – Private Lerngruppen und Projektteams
- **Responsive Design** – Optimiert für Desktop und Mobile
- **Datenschutzkonform** – Minimale Datenspeicherung, DSGVO-konform

## Technologie-Stack

| Komponente | Technologie |
|------------|-------------|
| Backend | PHP 8.x |
| Datenbank | MySQL / MariaDB |
| Frontend | HTML5, CSS3, JavaScript |
| Server | Apache (XAMPP) |
| Fonts | Rubik, Exo2 |

## Installation

### Voraussetzungen

- PHP 8.0 oder höher
- MySQL 5.7+ / MariaDB 10.3+
- Apache Webserver (oder XAMPP/MAMP)
- PDO MySQL Extension

### Einrichtung

1. **Repository klonen**
   ```bash
   git clone https://github.com/[username]/dhbw-chat.git
   cd dhbw-chat
   ```

2. **Datenbank einrichten**
   ```sql
   CREATE DATABASE dhbw_chat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Datenbankverbindung konfigurieren**
   
   Bearbeite `components/db_connect.php` mit deinen Zugangsdaten:
   ```php
   $pdo = new PDO('mysql:host=localhost;dbname=dhbw_chat', 'username', 'password');
   ```

4. **Apache konfigurieren**
   
   Stelle sicher, dass `mod_rewrite` aktiviert ist und der DocumentRoot auf das Projektverzeichnis zeigt.

5. **Berechtigungen setzen**
   ```bash
   chmod 755 -R /path/to/dhbw-chat
   ```

## Funktionen

### Benutzerauthentifizierung

- Registrierung mit DHBW E-Mail-Adresse
- Auswahl von Fakultät, Studiengang und Jahrgang
- Sichere Passwort-Speicherung mit `password_hash()`
- Session-basierte Authentifizierung

### Gastzugang

Nutzer können ohne Registrierung per Gruppencode beitreten:
```
chat.php?groupcode=ABC123
```

### Chat-System

- Echtzeit-Nachrichtenaustausch
- Globaler Chat für alle registrierten Nutzer
- Private Direktnachrichten
- Gruppenchats mit Mitgliederverwaltung
- Wichtige Notizen / Ablage-Funktion

### Profileinstellungen

- Profilbild ändern
- Benutzername bearbeiten
- E-Mail-Adresse aktualisieren
- Passwort ändern

## CSS-Architektur

Die Anwendung verwendet CSS Custom Properties für konsistentes Theming:

```css
:root {
    --primary-color: #FFFFFF;
    --primary-background-color: #202025;
    --primary-button-color: rgba(226,0,26,1);
    --error-color: #B50015;
    /* ... */
}
```

### Utility-Klassen

| Klasse | Beschreibung |
|--------|--------------|
| `.margin-top-X` | Oberer Abstand (1-10) |
| `.padding-X` | Innenabstand (1-5) |
| `.align-center` | Zentrierter Text |
| `.style-bold` | Fettschrift |
| `.background` | Sekundäre Hintergrundfarbe |

## Sicherheit

- **Session-Management**: Sichere Session-Handling mit `session_start()`
- **Input-Validierung**: Serverseitige Validierung aller Benutzereingaben
- **Prepared Statements**: PDO mit parametrisierten Queries gegen SQL-Injection
- **XSS-Schutz**: Output-Encoding mit `htmlspecialchars()`
- **IP-Logging**: Temporäre Speicherung (max. 24h) zur Missbrauchsprävention

## Entwicklung

### Debug-Modus

Für Entwicklungszwecke existiert ein Force-Login:
```
index.php?forceLogin=1
```

> ⚠️ **Wichtig**: Diese Funktion vor dem Produktivbetrieb entfernen!

### Datenbanktest

Die Datei `test.php` prüft die PHP-Konfiguration und Datenbankverbindung.

## Mitwirkende

Entwickelt im Rahmen eines DHBW-Projekts.

## Lizenz

Dieses Projekt ist für Bildungszwecke an der DHBW entwickelt worden.
