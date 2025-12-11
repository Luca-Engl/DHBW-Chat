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
| Datenbank | MySQL |
| Frontend | HTML5, CSS3, JavaScript |
| Server | Apache (XAMPP) |
| Fonts | Rubik, Exo2 |

## Installation

### Voraussetzungen

- PHP 8.0 oder höher
- MySQL 5.7+
- Apache Webserver (oder XAMPP/MAMP)
- PDO MySQL Extension

### Einrichtung

### 1. XAMPP herunterladen und starten

1. Neueste Version von XAMPP herunterladen:  
   https://www.apachefriends.org/de/download.html
2. Das XAMPP Control Panel ausführen.
3. Den Service **„Apache“** starten.
4. Den Service **„MySQL“** starten (ggf. als Admin bestätigen).

### 2. phpMyAdmin öffnen

- Klick auf **„Admin“** bei MySQL im XAMPP Control Panel.

### 3. Neues Benutzerkonto mit Datenbank anlegen

Unter **Benutzerkonten → Benutzerkonto hinzufügen** folgende Daten eintragen:

- **Benutzername:** `web-eng_dhbw-chat`  
- **Hostname:** beliebig / lokal (`127.0.0.1`)  
- **Passwort:** `chat`  
- **Option aktivieren:** „Erstelle eine Datenbank mit gleichem Namen und gewähre alle Rechte.“

Dadurch entstehen:

- **Datenbankname:** `web-eng_dhbw-chat`  
- **Benutzername:** `web-eng_dhbw-chat`  
- **Passwort:** `chat`  

### 4. Datenbankschema importieren

1. Die neu erstellte Datenbank links auswählen.
2. Oben den Reiter **„SQL“** anklicken.
3. Dateiinhalt von `src/sql/schema.sql` kopieren.
4. In die SQL-Befehls-Eingabemaske einfügen.
5. Mit **OK** ausführen.

### 5. Datenbankverbindung konfigurieren

In `components/db_connect.php` eintragen:

```php
$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=web-eng_dhbw-chat;charset=utf8mb4',
    'web-eng_dhbw-chat',
    'chat'
);
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
