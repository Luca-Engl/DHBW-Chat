<?php
require_once __DIR__ . '/api_init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/chat_helpers.php';
require_once __DIR__ . '/json_response.php';

$user_id = requireLogin();
$note_id = validateNoteId($_POST['note_id'] ?? 0);

try
{
    $stmt = $pdo->prepare("SELECT chat_id FROM note WHERE id = ?");
    $stmt->execute(array($note_id));
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$note)
    {
        jsonError('Notiz nicht gefunden');
    }

    requireChatAccess($pdo, $note['chat_id'], $user_id);

    $stmt = $pdo->prepare("DELETE FROM note WHERE id = ?");
    $stmt->execute(array($note_id));

    jsonSuccess([], 'Notiz gelöscht');
}
catch (PDOException $e)
{
    jsonError('Fehler beim Löschen der Notiz');
}