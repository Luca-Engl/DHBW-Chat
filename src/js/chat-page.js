/* ==========================================
   TEIL 1: CHAT FUNKTIONEN (Nachrichten & Scrollen)
   ========================================== */

/**
 * Scrollt den Chat automatisch nach unten.
 */
function scrollToBottom() {
    const chatHistory = document.getElementById("chat-history");
    if (chatHistory) {
        chatHistory.scrollTop = chatHistory.scrollHeight;
    }
}

/**
 * Sendet eine Nachricht und fügt sie dem Chat hinzu.
 * Wird jetzt per Event Listener aufgerufen.
 */
function sendMessage(event) {
    if (event) event.preventDefault(); // Verhindert das Neuladen der Seite

    const inputField = document.getElementById("chatmessage");
    const text = inputField.value.trim();

    if (text !== "") {
        const chatHistory = document.getElementById("chat-history");

        // Zeitstempel generieren (HH:MM)
        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' +
            now.getMinutes().toString().padStart(2, '0');

        // Neue Bubble HTML
        const newMessageHTML = `
            <div class="message sent">
                <span class="timestamp">${timeString}</span>
                <div class="bubble">
                    ${text}
                </div>
            </div>
        `;

        chatHistory.insertAdjacentHTML('beforeend', newMessageHTML);
        inputField.value = "";
        scrollToBottom();
    }
}


/* ==========================================
   TEIL 2: MODAL & EINSTELLUNGEN
   ========================================== */

// --- Einstellungen ---
function openSettings() {
    document.getElementById('settingsModal').classList.add('active');
}

function closeSettings() {
    document.getElementById('settingsModal').classList.remove('active');
}

// --- Kontakte ---
function openAddContact() {
    document.getElementById('addContactModal').classList.add('active');
}

function closeAddContact() {
    document.getElementById('addContactModal').classList.remove('active');
    const emailField = document.getElementById('contactEmail');
    if(emailField) emailField.value = '';
}

function addContact() {
    const emailField = document.getElementById('contactEmail');
    const email = emailField ? emailField.value : '';

    if (email === '') {
        alert('Bitte gib eine E-Mail-Adresse ein!');
        return;
    }

    alert('Kontakt ' + email + ' hinzugefügt!');
    closeAddContact();
}

// --- Gruppen ---
let groupMembers = [];

function openAddGroup() {
    groupMembers = [];
    updateMemberList();
    document.getElementById('addGroupModal').classList.add('active');
}

function closeAddGroup() {
    document.getElementById('addGroupModal').classList.remove('active');
    const nameField = document.getElementById('groupName');
    if(nameField) nameField.value = '';
    const memberField = document.getElementById('memberEmail');
    if(memberField) memberField.value = '';
    groupMembers = [];
}

function addMemberToList() {
    const memberField = document.getElementById('memberEmail');
    const email = memberField.value.trim();

    if (email === '') {
        alert('Bitte gib eine E-Mail-Adresse ein!');
        return;
    }
    if (groupMembers.includes(email)) {
        alert('Diese E-Mail wurde bereits hinzugefügt!');
        return;
    }
    groupMembers.push(email);
    memberField.value = '';
    updateMemberList();
}

function removeMember(email) {
    groupMembers = groupMembers.filter(member => member !== email);
    updateMemberList();
}

function updateMemberList() {
    const listDiv = document.getElementById('memberList');
    if (!listDiv) return;

    if (groupMembers.length === 0) {
        listDiv.innerHTML = '<p style="color: #888; font-size: 0.9rem;">Keine Mitglieder hinzugefügt</p>';
        return;
    }

    let html = '';
    groupMembers.forEach(email => {
        html += `
        <div class="member-item" style="display:flex; justify-content:space-between; margin-bottom:5px;">
            <span class="member-email">${email}</span>
            <button class="member-remove button-secondary" onclick="removeMember('${email}')" title="Entfernen" style="padding:2px 8px; font-size:0.8rem;">×</button>
        </div>
        `;
    });

    listDiv.innerHTML = html;
}

function createGroup() {
    const groupNameField = document.getElementById('groupName');
    const groupName = groupNameField.value.trim();

    if (groupName === '') {
        alert('Bitte gib einen Gruppennamen ein!');
        return;
    }
    if (groupMembers.length === 0) {
        alert('Bitte füge mindestens ein Mitglied hinzu!');
        return;
    }

    alert(`Gruppe "${groupName}" mit ${groupMembers.length} Mitglied(ern) erstellt!`);
    closeAddGroup();
}


/* ==========================================
   TEIL 3: EVENT LISTENER (Initialisierung)
   ========================================== */

document.addEventListener('DOMContentLoaded', function() {

    // 1. Chat sofort nach unten scrollen
    scrollToBottom();

    // 2. Formular-Logik: Verhindert das Neuladen
    const chatForm = document.getElementById('chatForm');
    if (chatForm) {
        chatForm.addEventListener('submit', sendMessage);
    } else {
        console.error("Fehler: Chat-Formular mit ID 'chatForm' nicht gefunden!");
    }

    // 3. Klicks außerhalb der Modals schließen diese
    const modals = ['settingsModal', 'addContactModal', 'addGroupModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    if(modalId === 'settingsModal') closeSettings();
                    if(modalId === 'addContactModal') closeAddContact();
                    if(modalId === 'addGroupModal') closeAddGroup();
                }
            });
        }
    });
});