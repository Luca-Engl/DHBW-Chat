
    function openSettings() {
    document.getElementById('settingsModal').classList.add('active');
}

    function closeSettings() {
    document.getElementById('settingsModal').classList.remove('active');
}

    function saveSettings() {
    alert('Einstellungen gespeichert!');
    closeSettings();
}

    document.getElementById('settingsModal').addEventListener('click', function(e) {
    if (e.target === this) {
    closeSettings();
}
});

    function openAddContact() {
        document.getElementById('addContactModal').classList.add('active');
    }

    function closeAddContact() {
        document.getElementById('addContactModal').classList.remove('active');
        document.getElementById('contactEmail').value = ''; // Feld leeren
    }

    function addContact() {
        const email = document.getElementById('contactEmail').value;

        if (email === '') {
            alert('Bitte gib eine E-Mail-Adresse ein!');
            return;
        }

        alert('Kontakt ' + email + ' hinzugefügt!');
        closeAddContact();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const contactModal = document.getElementById('addContactModal');
        if (contactModal) {
            contactModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAddContact();
                }
            });
        }
    });


    let groupMembers = [];

    function openAddGroup() {
        groupMembers = [];
        updateMemberList();
        document.getElementById('addGroupModal').classList.add('active');
    }

    function closeAddGroup() {
        document.getElementById('addGroupModal').classList.remove('active');
        document.getElementById('groupName').value = '';
        document.getElementById('memberEmail').value = '';
        groupMembers = [];
    }

    function addMemberToList() {
        const email = document.getElementById('memberEmail').value.trim();

        if (email === '') {
            alert('Bitte gib eine E-Mail-Adresse ein!');
            return;
        }

        if (groupMembers.includes(email)) {
            alert('Diese E-Mail wurde bereits hinzugefügt!');
            return;
        }

        groupMembers.push(email);
        document.getElementById('memberEmail').value = ''; // Feld leeren
        updateMemberList();
    }

    function removeMember(email) {
        groupMembers = groupMembers.filter(member => member !== email);
        updateMemberList();
    }

    function updateMemberList() {
        const listDiv = document.getElementById('memberList');

        if (groupMembers.length === 0) {
            listDiv.innerHTML = '<p style="color: #888; font-size: 0.9rem;">Keine Mitglieder hinzugefügt</p>';
            return;
        }

        let html = '';
        groupMembers.forEach(email => {
            html += `
            <div class="member-item">
                <span class="member-email">${email}</span>
                <button class="member-remove" onclick="removeMember('${email}')" title="Entfernen">×</button>
            </div>
        `;
        });

        listDiv.innerHTML = html;
    }

    function createGroup() {
        const groupName = document.getElementById('groupName').value.trim();

        if (groupName === '') {
            alert('Bitte gib einen Gruppennamen ein!');
            return;
        }

        if (groupMembers.length === 0) {
            alert('Bitte füge mindestens ein Mitglied hinzu!');
            return;
        }

        alert(`Gruppe "${groupName}" mit ${groupMembers.length} Mitglied(ern) erstellt!`);
        console.log('Gruppenname:', groupName);
        console.log('Mitglieder:', groupMembers);

        closeAddGroup();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const groupModal = document.getElementById('addGroupModal');
        if (groupModal) {
            groupModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAddGroup();
                }
            });
        }
    });

    let currentChat = 'Globalchat'; // Aktueller Chat

    // Panel öffnen/schließen
    function toggleImportantPanel() {
        const panel = document.getElementById('importantPanel');
        panel.classList.toggle('active');

        if (panel.classList.contains('active')) {
            loadNotes();
        }
    }

    // Notiz hinzufügen
    function addNote() {
        const input = document.getElementById('newNoteInput');
        const text = input.value.trim();

        if (text === '') {
            alert('Bitte gib eine Notiz ein!');
            return;
        }

        // Notizen aus localStorage laden
        const allNotes = JSON.parse(localStorage.getItem('chatNotes') || '{}');

        // Notizen für aktuellen Chat
        if (!allNotes[currentChat]) {
            allNotes[currentChat] = [];
        }

        // Neue Notiz hinzufügen
        allNotes[currentChat].push({
            id: Date.now(),
            text: text,
            date: new Date().toLocaleString('de-DE')
        });

        // Speichern
        localStorage.setItem('chatNotes', JSON.stringify(allNotes));

        // Liste aktualisieren
        loadNotes();

        // Input leeren
        input.value = '';
    }

    // Notizen für aktuellen Chat laden
    function loadNotes() {
        const allNotes = JSON.parse(localStorage.getItem('chatNotes') || '{}');
        const notes = allNotes[currentChat] || [];
        const list = document.getElementById('notesList');

        if (notes.length === 0) {
            list.innerHTML = '<p class="empty-state">Keine wichtigen Notizen für diesen Chat</p>';
            return;
        }

        list.innerHTML = notes.map(note => `
        <div class="note-item">
            <div class="note-content">
                <p class="note-text">${note.text}</p>
                <small class="note-date">${note.date}</small>
            </div>
            <button class="note-delete-btn" onclick="deleteNote(${note.id})" title="Löschen">×</button>
        </div>
    `).join('');
    }

    // Notiz löschen
    function deleteNote(id) {
        const allNotes = JSON.parse(localStorage.getItem('chatNotes') || '{}');

        if (allNotes[currentChat]) {
            allNotes[currentChat] = allNotes[currentChat].filter(note => note.id !== id);
            localStorage.setItem('chatNotes', JSON.stringify(allNotes));
            loadNotes();
        }
    }

    // Chat wechseln (später für die Chat-Liste)
    function switchChat(chatName) {
        currentChat = chatName;
        document.getElementById('currentChatName').textContent = chatName;
        loadNotes(); // Notizen des neuen Chats laden
    }