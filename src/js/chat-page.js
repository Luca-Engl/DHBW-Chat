
    function openSettings() {
    document.getElementById('settingsModal').classList.add('active');
}

    function closeSettings() {
    document.getElementById('settingsModal').classList.remove('active');
}

    function saveSettings() {
    // Hier kannst du die Einstellungen speichern
    alert('Einstellungen gespeichert!');
    closeSettings();
}

    // Modal schließen wenn außerhalb geklickt wird
    document.getElementById('settingsModal').addEventListener('click', function(e) {
    if (e.target === this) {
    closeSettings();
}
});