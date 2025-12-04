function scrollToBottom()
{
    const chatHistory = document.getElementById("chat-history");
    if (chatHistory)
    {
        chatHistory.scrollTop = chatHistory.scrollHeight;
    }
}

function sendMessage(event)
{
    if (event) event.preventDefault();

    const inputField = document.getElementById("chatmessage");
    const text = inputField.value.trim();

    if (text !== "")
    {
        const chatHistory = document.getElementById("chat-history");

        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' +
            now.getMinutes().toString().padStart(2, '0');

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

function openSettings()
{
    document.getElementById('settingsModal').classList.add('active');
}

function closeSettings()
{
    document.getElementById('settingsModal').classList.remove('active');
}

function openEditUsername()
{
    document.getElementById('editUsernameModal').classList.add('active');
    document.getElementById('newUsername').value = '';
    document.getElementById('username-error').classList.add('hidden');
    document.getElementById('username-success').classList.add('hidden');
}

function closeEditUsername()
{
    document.getElementById('editUsernameModal').classList.remove('active');
}

function openEditEmail()
{
    document.getElementById('editEmailModal').classList.add('active');
    document.getElementById('newEmail').value = '';
    document.getElementById('email-error').classList.add('hidden');
    document.getElementById('email-success').classList.add('hidden');
}

function closeEditEmail()
{
    document.getElementById('editEmailModal').classList.remove('active');
}

function openEditPassword()
{
    document.getElementById('editPasswordModal').classList.add('active');
    document.getElementById('oldPassword').value = '';
    document.getElementById('newPassword').value = '';
    document.getElementById('newPasswordConfirm').value = '';
    document.getElementById('password-error').classList.add('hidden');
    document.getElementById('password-success').classList.add('hidden');
}

function closeEditPassword()
{
    document.getElementById('editPasswordModal').classList.remove('active');
}

function updateUsername()
{
    var newUsername = document.getElementById('newUsername').value.trim();
    var errorDiv = document.getElementById('username-error');
    var successDiv = document.getElementById('username-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    if (!newUsername)
    {
        errorDiv.textContent = 'Bitte gib einen Benutzernamen ein';
        errorDiv.classList.remove('hidden');
        return;
    }

    var formData = new FormData();
    formData.append('action', 'update_username');
    formData.append('new_username', newUsername);

    fetch('../components/update_user.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                successDiv.textContent = data.message;
                successDiv.classList.remove('hidden');
                document.getElementById('username-display').textContent = data.new_username;
                document.querySelector('.nav-username').textContent = data.new_username;
                setTimeout(function()
                {
                    closeEditUsername();
                }, 1500);
            }
            else
            {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(function(error)
        {
            errorDiv.textContent = 'Ein Fehler ist aufgetreten';
            errorDiv.classList.remove('hidden');
        });
}

function updateEmail()
{
    var newEmail = document.getElementById('newEmail').value.trim();
    var errorDiv = document.getElementById('email-error');
    var successDiv = document.getElementById('email-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    if (!newEmail)
    {
        errorDiv.textContent = 'Bitte gib eine E-Mail ein';
        errorDiv.classList.remove('hidden');
        return;
    }

    var formData = new FormData();
    formData.append('action', 'update_email');
    formData.append('new_email', newEmail);

    fetch('../components/update_user.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                successDiv.textContent = data.message;
                successDiv.classList.remove('hidden');
                document.getElementById('email-display').textContent = data.new_email;
                setTimeout(function()
                {
                    closeEditEmail();
                }, 1500);
            }
            else
            {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(function(error)
        {
            errorDiv.textContent = 'Ein Fehler ist aufgetreten';
            errorDiv.classList.remove('hidden');
        });
}

function updatePassword()
{
    var oldPassword = document.getElementById('oldPassword').value;
    var newPassword = document.getElementById('newPassword').value;
    var newPasswordConfirm = document.getElementById('newPasswordConfirm').value;
    var errorDiv = document.getElementById('password-error');
    var successDiv = document.getElementById('password-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    if (!oldPassword || !newPassword || !newPasswordConfirm)
    {
        errorDiv.textContent = 'Bitte fülle alle Felder aus';
        errorDiv.classList.remove('hidden');
        return;
    }

    if (newPassword !== newPasswordConfirm)
    {
        errorDiv.textContent = 'Neue Passwörter stimmen nicht überein';
        errorDiv.classList.remove('hidden');
        return;
    }

    var formData = new FormData();
    formData.append('action', 'update_password');
    formData.append('old_password', oldPassword);
    formData.append('new_password', newPassword);
    formData.append('new_password_confirm', newPasswordConfirm);

    fetch('../components/update_user.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                successDiv.textContent = data.message;
                successDiv.classList.remove('hidden');
                document.getElementById('oldPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('newPasswordConfirm').value = '';
                setTimeout(function()
                {
                    closeEditPassword();
                }, 1500);
            }
            else
            {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(function(error)
        {
            errorDiv.textContent = 'Ein Fehler ist aufgetreten';
            errorDiv.classList.remove('hidden');
        });
}

function openAddContact()
{
    document.getElementById('addContactModal').classList.add('active');
}

function closeAddContact()
{
    document.getElementById('addContactModal').classList.remove('active');
    const emailField = document.getElementById('contactEmail');
    if(emailField) emailField.value = '';
}

function addContact()
{
    const emailField = document.getElementById('contactEmail');
    const email = emailField ? emailField.value : '';

    if (email === '')
    {
        alert('Bitte gib eine E-Mail-Adresse ein!');
        return;
    }

    alert('Kontakt ' + email + ' hinzugefügt!');
    closeAddContact();
}

let groupMembers = [];

function openAddGroup()
{
    groupMembers = [];
    updateMemberList();
    document.getElementById('addGroupModal').classList.add('active');
}

function closeAddGroup()
{
    document.getElementById('addGroupModal').classList.remove('active');
    const nameField = document.getElementById('groupName');
    if(nameField) nameField.value = '';
    const memberField = document.getElementById('memberEmail');
    if(memberField) memberField.value = '';
    groupMembers = [];
}

function addMemberToList()
{
    const memberField = document.getElementById('memberEmail');
    const email = memberField.value.trim();

    if (email === '')
    {
        alert('Bitte gib eine E-Mail-Adresse ein!');
        return;
    }
    if (groupMembers.includes(email))
    {
        alert('Diese E-Mail wurde bereits hinzugefügt!');
        return;
    }
    groupMembers.push(email);
    memberField.value = '';
    updateMemberList();
}

function removeMember(email)
{
    groupMembers = groupMembers.filter(function(member)
    {
        return member !== email;
    });
    updateMemberList();
}

function updateMemberList()
{
    const listDiv = document.getElementById('memberList');
    if (!listDiv) return;

    if (groupMembers.length === 0)
    {
        listDiv.innerHTML = '<p style="color: #888; font-size: 0.9rem;">Keine Mitglieder hinzugefügt</p>';
        return;
    }

    let html = '';
    groupMembers.forEach(function(email)
    {
        html += `
        <div class="member-item" style="display:flex; justify-content:space-between; margin-bottom:5px;">
            <span class="member-email">${email}</span>
            <button class="member-remove button-secondary" onclick="removeMember('${email}')" title="Entfernen" style="padding:2px 8px; font-size:0.8rem;">×</button>
        </div>
        `;
    });

    listDiv.innerHTML = html;
}

function createGroup()
{
    const groupNameField = document.getElementById('groupName');
    const groupName = groupNameField.value.trim();

    if (groupName === '')
    {
        alert('Bitte gib einen Gruppennamen ein!');
        return;
    }
    if (groupMembers.length === 0)
    {
        alert('Bitte füge mindestens ein Mitglied hinzu!');
        return;
    }

    alert(`Gruppe "${groupName}" mit ${groupMembers.length} Mitglied(ern) erstellt!`);
    closeAddGroup();
}

let currentChat = 'Globalchat';

function toggleImportantPanel()
{
    const panel = document.getElementById('importantPanel');
    panel.classList.toggle('active');

    if (panel.classList.contains('active'))
    {
        loadNotes();
    }
}

function addNote()
{
    const input = document.getElementById('newNoteInput');
    const text = input.value.trim();

    if (text === '')
    {
        return;
    }

    const allNotes = JSON.parse(localStorage.getItem('chatNotes') || '{}');

    if (!allNotes[currentChat])
    {
        allNotes[currentChat] = [];
    }

    allNotes[currentChat].push({
        id: Date.now(),
        text: text,
        date: new Date().toLocaleString('de-DE')
    });

    localStorage.setItem('chatNotes', JSON.stringify(allNotes));
    loadNotes();
    input.value = '';
    input.style.height = 'auto';
}

function deleteNote(id)
{
    const allNotes = JSON.parse(localStorage.getItem('chatNotes') || '{}');

    if (allNotes[currentChat])
    {
        allNotes[currentChat] = allNotes[currentChat].filter(function(note)
        {
            return note.id !== id;
        });
        localStorage.setItem('chatNotes', JSON.stringify(allNotes));
        loadNotes();
    }
}

function switchChat(chatName)
{
    currentChat = chatName;
    document.getElementById('currentChatName').textContent = chatName;
    loadNotes();
}

function loadNotes()
{
    const allNotes = JSON.parse(localStorage.getItem('chatNotes') || '{}');
    const notes = allNotes[currentChat] || [];
    const list = document.getElementById('notesList');

    if (notes.length === 0)
    {
        list.innerHTML = '<p class="empty-state">Keine wichtigen Notizen für diesen Chat</p>';
        return;
    }

    list.innerHTML = notes.map(function(note)
    {
        return `
        <div class="note-item">
            <div class="note-content">
                <p class="note-text">${note.text}</p>
                <small class="note-date">${note.date}</small>
            </div>
            <button class="note-delete-btn" onclick="deleteNote(${note.id})" title="Löschen">×</button>
        </div>
    `;
    }).join('');

    setTimeout(function()
    {
        list.scrollTop = list.scrollHeight;
    }, 50);
}

function isMobile()
{
    return window.innerWidth <= 768;
}

function openChat(chatName)
{
    const container = document.querySelector('.chat-container');
    const chatTitle = document.getElementById('currentChatName');

    if (chatTitle && chatName)
    {
        chatTitle.textContent = chatName;
    }

    if (isMobile())
    {
        container.classList.add('chat-open');
    }

    switchChat(chatName);
}

function closeChat()
{
    const container = document.querySelector('.chat-container');
    container.classList.remove('chat-open');

    const panel = document.getElementById('importantPanel');
    if (panel)
    {
        panel.classList.remove('active');
    }
}

window.addEventListener('resize', function()
{
    if (!isMobile())
    {
        const container = document.querySelector('.chat-container');
        container.classList.remove('chat-open');
    }
});

function autoResizeTextarea(textarea)
{
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, parseInt(getComputedStyle(textarea).maxHeight)) + 'px';
}

document.addEventListener('DOMContentLoaded', function()
{
    scrollToBottom();

    const chatForm = document.getElementById('chatForm');
    if (chatForm)
    {
        chatForm.addEventListener('submit', sendMessage);
    }

    const modals = ['settingsModal', 'addContactModal', 'addGroupModal', 'editUsernameModal', 'editEmailModal', 'editPasswordModal'];
    modals.forEach(function(modalId)
    {
        const modal = document.getElementById(modalId);
        if (modal)
        {
            modal.addEventListener('click', function(e)
            {
                if (e.target === this)
                {
                    if(modalId === 'settingsModal') closeSettings();
                    if(modalId === 'addContactModal') closeAddContact();
                    if(modalId === 'addGroupModal') closeAddGroup();
                    if(modalId === 'editUsernameModal') closeEditUsername();
                    if(modalId === 'editEmailModal') closeEditEmail();
                    if(modalId === 'editPasswordModal') closeEditPassword();
                }
            });
        }
    });

    document.addEventListener('error', function(event)
    {
        const target = event.target;

        if (target.tagName === 'IMG')
        {
            if (target.classList.contains('img-logo-nav'))
            {
                target.style.display = 'none';
            }
            else if (target.alt === 'User-Avatar' || target.alt === 'Avatar' || target.classList.contains('user-avatar-img'))
            {
                if (!target.src.includes('placeholder.com'))
                {
                    target.src = 'https://via.placeholder.com/40';
                }
            }
        }
    }, true);

    const chatInput = document.getElementById('chatmessage');
    if (chatInput)
    {
        chatInput.addEventListener('keydown', function(e)
        {
            if (e.key === 'Enter' && !e.shiftKey)
            {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    const noteInput = document.getElementById('newNoteInput');
    if (noteInput)
    {
        noteInput.addEventListener('input', function()
        {
            autoResizeTextarea(this);
        });

        noteInput.addEventListener('keydown', function(e)
        {
            if (e.key === 'Enter' && !e.shiftKey)
            {
                e.preventDefault();
                addNote();
                this.style.height = 'auto';
            }
        });
    }

    const chatItems = document.querySelectorAll('.chat-sidebar li');
    chatItems.forEach(function(item)
    {
        item.addEventListener('click', function()
        {
            chatItems.forEach(function(i)
            {
                i.classList.remove('active-chat');
            });
            this.classList.add('active-chat');
            openChat(this.textContent.trim());
        });
    });
});