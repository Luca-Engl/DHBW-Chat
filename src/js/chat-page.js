let currentChatId = null;
let currentChat = 'Globalchat';
let messageCheckInterval = null;
let currentManageGroupId = null;
let editingMessageId = null;
let deletingMessageId = null;
let pendingGroupMemberRemovalId = null;
let pendingGroupMemberRemovalBtn = null;
let pendingGroupMemberRemovalTimer = null;

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function validateId(id) {
    const num = parseInt(id);
    return !isNaN(num) && num > 0 ? num : null;
}

function buildUrl(baseUrl, params) {
    const url = new URL(baseUrl, window.location.origin);
    Object.keys(params).forEach(key => {
        url.searchParams.append(key, params[key]);
    });
    return url.toString();
}

function loadChats()
{
    if (isGuest && guestChatId) {
        console.log('Gast-Modus: Chat bereits geladen');

        currentChatId = guestChatId;
        currentChat = guestChatName || 'Gruppenchat';

        const guestChatItem = document.querySelector('.chat-item[data-chat-id="' + guestChatId + '"]');
        if (guestChatItem) {
            guestChatItem.classList.add('active-chat');

            document.getElementById('currentChatName').textContent = guestChatName || 'Gruppenchat';

            updateManageButton('group', guestChatId, guestChatName);

            loadMessages(guestChatId);
            startAutoReload();

            guestChatItem.addEventListener('click', function() {
                loadMessages(guestChatId);
                startAutoReload();

                const panel = document.getElementById('importantPanel');
                if (panel && panel.classList.contains('active')) {
                    loadNotes();
                }
            });
        }

        return;
    }

    fetch('/src/components/get_chats.php')
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                const chatList = document.getElementById('chatList');

                if (data.chats.length === 0)
                {
                    chatList.innerHTML = '<li style="color: #888;">Keine Chats verfügbar</li>';
                    return;
                }

                chatList.innerHTML = '';

                data.chats.forEach(function(chat, index)
                {
                    const li = document.createElement('li');
                    li.setAttribute('data-chat-id', chat.id);
                    li.setAttribute('data-chat-name', chat.chat_name);
                    li.setAttribute('data-chat-type', chat.chat_type);
                    li.classList.add('chat-item');

                    let icon = '💬 | ';
                    if (chat.chat_type === 'global') {
                        icon = '🌐 | ';
                    } else if (chat.chat_type === 'group') {
                        icon = '👥 | ';
                    }

                    li.innerHTML = '<span class="chat-icon">' + icon + '</span><span class="chat-name">' + escapeHtml(chat.chat_name) + '</span>';

                    if (index === 0)
                    {
                        li.classList.add('active-chat');
                        currentChatId = chat.id;
                        currentChat = chat.chat_name;
                        document.getElementById('currentChatName').textContent = chat.chat_name;
                        updateManageButton(chat.chat_type, chat.id, chat.chat_name);
                        loadMessages(chat.id);
                        startAutoReload();
                    }

                    li.addEventListener('click', function()
                    {
                        const allItems = document.querySelectorAll('#chatList li');
                        allItems.forEach(function(item)
                        {
                            item.classList.remove('active-chat');
                        });

                        this.classList.add('active-chat');

                        const chatId = parseInt(this.getAttribute('data-chat-id'));
                        const chatName = this.getAttribute('data-chat-name');
                        const chatType = this.getAttribute('data-chat-type');

                        currentChatId = chatId;
                        currentChat = chatName;

                        document.getElementById('currentChatName').textContent = chatName;
                        updateManageButton(chatType, chatId, chatName);

                        loadMessages(chatId);
                        startAutoReload();

                        const panel = document.getElementById('importantPanel');
                        if (panel && panel.classList.contains('active'))
                        {
                            loadNotes();
                        }

                        if (isMobile())
                        {
                            document.querySelector('.chat-container').classList.add('chat-open');
                        }
                    });

                    chatList.appendChild(li);
                });
            }
            else
            {
                document.getElementById('chatList').innerHTML = '<li style="color: #c33;">Fehler: ' + data.message + '</li>';
            }
        })
        .catch(function(error)
        {
            console.error('Error loading chats:', error);
            document.getElementById('chatList').innerHTML = '<li style="color: #c33;">Fehler beim Laden</li>';
        });
}

function updateManageButton(chatType, chatId, chatName)
{
    const manageBtn = document.getElementById('manageGroupBtn');

    if (chatType === 'group')
    {
        manageBtn.style.display = 'inline-block';
        manageBtn.setAttribute('data-chat-id', chatId);
        manageBtn.setAttribute('data-chat-name', chatName);
    }
    else
    {
        manageBtn.style.display = 'none';
    }
}

function openManageGroupFromNav()
{
    const manageBtn = document.getElementById('manageGroupBtn');
    const chatId = parseInt(manageBtn.getAttribute('data-chat-id'));
    const chatName = manageBtn.getAttribute('data-chat-name');

    if (chatId && chatName)
    {
        openManageGroup(chatId, chatName);
    }
}

function loadMessages(chatId, isAutoReload)
{
    const validChatId = validateId(chatId);
    if (!validChatId) {
        console.error('Ungültige Chat-ID');
        return;
    }

    if (typeof isAutoReload === 'undefined')
    {
        isAutoReload = false;
    }

    const url = buildUrl('/src/components/get_messages.php', { chat_id: validChatId });

    fetch(url)
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (!data) {
                throw new Error('Keine Daten empfangen');
            }

            if (data.success)
            {
                const chatHistory = document.getElementById('chat-history');
                const currentUserId = data.current_user_id;
                const existingForm = document.getElementById('chatForm');

                if (!data.messages) {
                    data.messages = [];
                }

                const wasAtBottom = chatHistory.scrollHeight - chatHistory.scrollTop <= chatHistory.clientHeight + 100;

                let messagesHtml = '';

                if (data.messages.length === 0)
                {
                    messagesHtml = '<p style="text-align: center; color: #888; padding: 20px;">Hier sieht es leer aus ... Sei der erste, der eine Nachricht schreibt!</p>';
                }
                else
                {
                    data.messages.forEach(function(msg)
                    {
                        const isSent = msg.sender_id === currentUserId;
                        const messageClass = isSent ? 'sent' : 'received';

                        const date = new Date(msg.sent_at);
                        const timeString = date.getHours().toString().padStart(2, '0') + ':' +
                            date.getMinutes().toString().padStart(2, '0');

                        const senderInfo = !isSent ? '<strong>' + escapeHtml(msg.sender_name) + '</strong><br>' : '';

                        let editButton = '';
                        let deleteButton = '';
                        if (isSent) {
                            const contentBase64 = btoa(unescape(encodeURIComponent(msg.content)));
                            editButton = `<button class="edit-message-btn" data-message-id="${msg.id}" data-content="${contentBase64}" title="Nachricht bearbeiten">✏️</button>`;
                            deleteButton = `<button class="delete-message-btn" data-message-id="${msg.id}" title="Nachricht löschen">🗑️</button>`;
                        }

                        let timeDisplay = '';
                        if (msg.edited_at) {
                            const editDate = new Date(msg.edited_at);
                            const editTimeString = editDate.getHours().toString().padStart(2, '0') + ':' +
                                editDate.getMinutes().toString().padStart(2, '0');
                            timeDisplay = `<span class="edited-time">${editTimeString} <span class="original-time">(${timeString})</span></span>`;
                        } else {
                            timeDisplay = `<span class="timestamp">${timeString}</span>`;
                        }

                        let footerContent = '';
                        if (isSent) {
                            footerContent = `
                                <section class="message-footer message-footer-sent">
                                    <section class="message-footer-left">
                                        ${editButton}
                                        ${deleteButton}
                                    </section>
                                    <section class="message-footer-right">
                                        ${timeDisplay}
                                    </section>
                                </section>
                            `;
                        } else {
                            footerContent = `
                                <section class="message-footer message-footer-received">
                                    ${timeDisplay}
                                </section>
                            `;
                        }

                        messagesHtml += `
                            <section class="message ${messageClass}" data-message-id="${msg.id}">
                                <section class="bubble">
                                    ${senderInfo}${escapeHtml(msg.content)}
                                </section>
                                ${footerContent}
                            </section>
                        `;
                    });
                }

                if (isAutoReload && existingForm)
                {
                    const messages = chatHistory.querySelectorAll('.message');
                    messages.forEach(function(msg)
                    {
                        msg.remove();
                    });

                    const emptyMsg = chatHistory.querySelector('p');
                    if (emptyMsg) emptyMsg.remove();

                    existingForm.insertAdjacentHTML('beforebegin', messagesHtml);
                }
                else
                {
                    messagesHtml += `
                    <form class="chat-input-container chat-input-floating" id="chatForm">
                        <label for="chatmessage" class="visually-hidden">Nachricht eingeben</label>
                        <textarea id="chatmessage" name="chatmessage" rows="1"
                                  placeholder="Neue Nachricht eingeben ..."
                                  inputmode="text" aria-label="Nachricht eingeben"></textarea>
                        <button type="submit" class="style-bold">Senden ➡ </button>
                    </form>
                `;

                    chatHistory.innerHTML = messagesHtml;

                    const chatForm = document.getElementById('chatForm');
                    if (chatForm)
                    {
                        chatForm.addEventListener('submit', function(e)
                        {
                            e.preventDefault();
                            sendMessage();
                        });
                    }
                }

                if (!isAutoReload || wasAtBottom)
                {
                    chatHistory.scrollTop = chatHistory.scrollHeight;
                }
            }
        })
        .catch(function(error)
        {
            console.error('Error loading messages:', error);
        });
}

function sendMessage()
{
    const messageInput = document.getElementById('chatmessage');
    const message = messageInput.value.trim();

    if (!message)
    {
        return;
    }

    const validChatId = validateId(currentChatId);
    if (!validChatId)
    {
        showToast('Bitte wähle einen Chat aus', 'error');
        return;
    }

    if (message.length > 2048) {
        showToast('Nachricht zu lang (max. 2048 Zeichen)', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('chat_id', validChatId);
    formData.append('content', message);

    fetch('/src/components/send_message.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                messageInput.value = '';
                loadMessages(currentChatId, true);
            }
            else
            {
                showToast('Fehler: ' + data.message, 'error');
            }
        })
        .catch(function(error)
        {
            console.error('Error sending message:', error);
            showToast('Fehler beim Senden der Nachricht', 'error');
        });
}

function startAutoReload()
{
    stopAutoReload();

    messageCheckInterval = setInterval(function()
    {
        if (currentChatId)
        {
            loadMessages(currentChatId, true);
        }
    }, 3000);
}

function stopAutoReload()
{
    if (messageCheckInterval)
    {
        clearInterval(messageCheckInterval);
        messageCheckInterval = null;
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
    document.getElementById('username-error').classList.add('hidden');
    document.getElementById('username-success').classList.add('hidden');
    document.getElementById('newUsername').value = '';
}

function closeEditUsername()
{
    document.getElementById('editUsernameModal').classList.remove('active');
}

function updateUsername()
{
    const newUsername = document.getElementById('newUsername').value.trim();
    const errorDiv = document.getElementById('username-error');
    const successDiv = document.getElementById('username-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    if (!newUsername)
    {
        errorDiv.textContent = 'Bitte gib einen Benutzernamen ein';
        errorDiv.classList.remove('hidden');
        return;
    }

    if (!/^[A-Za-z0-9]+$/.test(newUsername))
    {
        errorDiv.textContent = 'Nur Buchstaben und Zahlen erlaubt';
        errorDiv.classList.remove('hidden');
        return;
    }

    if (newUsername.length < 3 || newUsername.length > 30) {
        errorDiv.textContent = 'Benutzername muss 3-30 Zeichen lang sein';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_username');
    formData.append('new_username', newUsername);

    fetch('/src/components/update_user.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
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
            console.error('Error updating username:', error);
            errorDiv.textContent = 'Fehler beim Aktualisieren';
            errorDiv.classList.remove('hidden');
        });
}

function openEditEmail()
{
    document.getElementById('editEmailModal').classList.add('active');
    document.getElementById('email-error').classList.add('hidden');
    document.getElementById('email-success').classList.add('hidden');
    document.getElementById('newEmail').value = '';
}

function closeEditEmail()
{
    document.getElementById('editEmailModal').classList.remove('active');
}

function updateEmail()
{
    const newEmail = document.getElementById('newEmail').value.trim();
    const errorDiv = document.getElementById('email-error');
    const successDiv = document.getElementById('email-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    if (!newEmail)
    {
        errorDiv.textContent = 'Bitte gib eine E-Mail ein';
        errorDiv.classList.remove('hidden');
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(newEmail)) {
        errorDiv.textContent = 'Ungültige E-Mail-Adresse';
        errorDiv.classList.remove('hidden');
        return;
    }

    if (newEmail.length > 100) {
        errorDiv.textContent = 'E-Mail zu lang (max. 100 Zeichen)';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_email');
    formData.append('new_email', newEmail);

    fetch('/src/components/update_user.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
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
            console.error('Error updating email:', error);
            errorDiv.textContent = 'Fehler beim Aktualisieren';
            errorDiv.classList.remove('hidden');
        });
}

function openEditPassword()
{
    document.getElementById('editPasswordModal').classList.add('active');
    document.getElementById('password-error').classList.add('hidden');
    document.getElementById('password-success').classList.add('hidden');
    document.getElementById('oldPassword').value = '';
    document.getElementById('newPassword').value = '';
    document.getElementById('newPasswordConfirm').value = '';
}

function closeEditPassword()
{
    document.getElementById('editPasswordModal').classList.remove('active');
}

function updatePassword()
{
    const oldPassword = document.getElementById('oldPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const newPasswordConfirm = document.getElementById('newPasswordConfirm').value;
    const errorDiv = document.getElementById('password-error');
    const successDiv = document.getElementById('password-success');

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

    if (newPassword.length < 6 || newPassword.length > 30) {
        errorDiv.textContent = 'Passwort muss 6-30 Zeichen lang sein';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_password');
    formData.append('old_password', oldPassword);
    formData.append('new_password', newPassword);
    formData.append('new_password_confirm', newPasswordConfirm);

    fetch('/src/components/update_user.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                successDiv.textContent = data.message;
                successDiv.classList.remove('hidden');

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
            console.error('Error updating password:', error);
            errorDiv.textContent = 'Fehler beim Aktualisieren';
            errorDiv.classList.remove('hidden');
        });
}

function openAddContact()
{
    document.getElementById('addContactModal').classList.add('active');
    const inputField = document.getElementById('contactInput');
    if (inputField)
    {
        inputField.value = '';
        inputField.style.borderColor = '';
    }
    const errorDiv = document.getElementById('contact-error');
    const successDiv = document.getElementById('contact-success');
    if (errorDiv) errorDiv.classList.add('hidden');
    if (successDiv) successDiv.classList.add('hidden');
}

function closeAddContact()
{
    document.getElementById('addContactModal').classList.remove('active');
    const inputField = document.getElementById('contactInput');
    if (inputField)
    {
        inputField.value = '';
        inputField.style.borderColor = '';
    }
}

function addContact()
{
    const inputField = document.getElementById('contactInput');
    const input = inputField.value.trim();
    const errorDiv = document.getElementById('contact-error');
    const successDiv = document.getElementById('contact-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');
    inputField.style.borderColor = '';

    if (input === '')
    {
        errorDiv.textContent = 'Bitte gib einen Benutzernamen oder E-Mail ein!';
        errorDiv.classList.remove('hidden');
        inputField.style.borderColor = '#c33';
        return;
    }

    const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input);
    const isValidUsername = /^[A-Za-z0-9_.-]{3,30}$/.test(input);

    if (!isValidEmail && !isValidUsername)
    {
        errorDiv.textContent = 'Ungültiger Benutzername oder E-Mail. Benutzername: 3-30 Zeichen (Buchstaben, Zahlen, _, -, .)';
        errorDiv.classList.remove('hidden');
        inputField.style.borderColor = '#c33';
        return;
    }

    const formData = new FormData();
    formData.append('contact_input', input);

    fetch('/src/components/create_personal_chat.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                successDiv.textContent = 'Chat mit ' + data.chat_name + ' erstellt!';
                successDiv.classList.remove('hidden');
                inputField.style.borderColor = '';

                setTimeout(function()
                {
                    closeAddContact();
                    loadChats();

                    setTimeout(function()
                    {
                        const chatItems = document.querySelectorAll('#chatList li');
                        chatItems.forEach(function(item)
                        {
                            const itemChatId = parseInt(item.getAttribute('data-chat-id'));
                            if (itemChatId === data.chat_id)
                            {
                                item.click();
                            }
                        });
                    }, 500);
                }, 1000);
            }
            else
            {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(function(error)
        {
            console.error('Fetch Error:', error);
            errorDiv.textContent = 'Fehler beim Erstellen des Chats: ' + error.message;
            errorDiv.classList.remove('hidden');
        });
}

let groupMembers = [];

function openAddGroup()
{
    groupMembers = [];
    updateMemberList();
    document.getElementById('addGroupModal').classList.add('active');
    const nameField = document.getElementById('groupName');
    const memberField = document.getElementById('memberInput');
    if (nameField) nameField.value = '';
    if (memberField) {
        memberField.value = '';
        memberField.style.borderColor = '';
    }
    const errorDiv = document.getElementById('group-error');
    const successDiv = document.getElementById('group-success');
    if (errorDiv) errorDiv.classList.add('hidden');
    if (successDiv) successDiv.classList.add('hidden');
}

function closeAddGroup()
{
    document.getElementById('addGroupModal').classList.remove('active');
    const nameField = document.getElementById('groupName');
    if(nameField) nameField.value = '';
    const memberField = document.getElementById('memberInput');
    if(memberField) {
        memberField.value = '';
        memberField.style.borderColor = '';
    }
    groupMembers = [];
    updateMemberList();
}

function addMemberToList()
{
    const memberField = document.getElementById('memberInput');
    const input = memberField.value.trim();
    const errorDiv = document.getElementById('group-error');

    errorDiv.classList.add('hidden');
    memberField.style.borderColor = '';

    if (input === '')
    {
        return;
    }

    const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input);
    const isValidUsername = /^[A-Za-z0-9_.-]{3,30}$/.test(input);

    if (!isValidEmail && !isValidUsername)
    {
        errorDiv.textContent = 'Ungültiger Benutzername oder E-Mail. Benutzername: 3-30 Zeichen (Buchstaben, Zahlen, _, -, .)';
        errorDiv.classList.remove('hidden');
        memberField.style.borderColor = '#c33';
        return;
    }

    if (groupMembers.includes(input))
    {
        errorDiv.textContent = 'Dieses Mitglied wurde bereits hinzugefügt!';
        errorDiv.classList.remove('hidden');
        memberField.style.borderColor = '#c33';
        return;
    }

    groupMembers.push(input);
    memberField.value = '';
    memberField.style.borderColor = '';
    updateMemberList();
}

function removeMember(member)
{
    groupMembers = groupMembers.filter(function(m)
    {
        return m !== member;
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
    groupMembers.forEach(function(member)
    {
        html += `
        <div class="member-item" style="display:flex; justify-content:space-between; margin-bottom:5px;">
            <span class="member-email">${escapeHtml(member)}</span>
            <button class="member-remove button-secondary" onclick="removeMember('${escapeHtml(member)}')" title="✖️ Entfernen" style="padding:2px 8px; font-size:0.8rem;">×</button>
        </div>
        `;
    });

    listDiv.innerHTML = html;
}

function createGroup()
{
    const groupNameField = document.getElementById('groupName');
    const groupName = groupNameField.value.trim();
    const errorDiv = document.getElementById('group-error');
    const successDiv = document.getElementById('group-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    if (groupName === '')
    {
        errorDiv.textContent = 'Bitte gib einen Gruppennamen ein!';
        errorDiv.classList.remove('hidden');
        return;
    }

    if (groupName.length > 100) {
        errorDiv.textContent = 'Gruppenname zu lang (max. 100 Zeichen)';
        errorDiv.classList.remove('hidden');
        return;
    }

    if (groupMembers.length === 0)
    {
        errorDiv.textContent = 'Bitte füge mindestens ein Mitglied hinzu!';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('group_name', groupName);
    formData.append('members', JSON.stringify(groupMembers));

    fetch('/src/components/create_group_chat.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                successDiv.textContent = data.message;
                successDiv.classList.remove('hidden');

                setTimeout(function()
                {
                    closeAddGroup();
                    loadChats();

                    setTimeout(function()
                    {
                        const chatItems = document.querySelectorAll('#chatList li');
                        chatItems.forEach(function(item)
                        {
                            const itemChatId = parseInt(item.getAttribute('data-chat-id'));
                            if (itemChatId === data.chat_id)
                            {
                                item.click();
                            }
                        });
                    }, 500);
                }, 1000);
            }
            else
            {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(function(error)
        {
            console.error('Error creating group:', error);
            errorDiv.textContent = 'Fehler beim Erstellen der Gruppe';
            errorDiv.classList.remove('hidden');
        });
}

function openManageGroup(chatId, chatName)
{
    currentManageGroupId = chatId;
    document.getElementById('manageGroupModal').classList.add('active');

    const errorDiv = document.getElementById('manage-group-error');
    const successDiv = document.getElementById('manage-group-success');
    if (errorDiv) errorDiv.classList.add('hidden');
    if (successDiv) successDiv.classList.add('hidden');

    const inputField = document.getElementById('addMemberInput');
    if (inputField) inputField.value = '';

    loadGroupMembers(chatId);
    loadInviteCode(chatId);
}

function updateGroupName()
{
    const errorDiv = document.getElementById('manage-group-error');
    const successDiv = document.getElementById('manage-group-success');
    if (errorDiv) errorDiv.classList.add('hidden');
    if (successDiv) successDiv.classList.add('hidden');

    if (!currentManageGroupId)
    {
        if (errorDiv) {
            errorDiv.textContent = 'Keine Gruppe ausgewählt';
            errorDiv.classList.remove('hidden');
        }
        return;
    }

    const nameInput = document.getElementById('manageGroupNameInput');
    const newName = (nameInput ? nameInput.value : '').trim();

    if (!newName)
    {
        if (errorDiv) {
            errorDiv.textContent = 'Bitte gib einen Gruppennamen ein';
            errorDiv.classList.remove('hidden');
        }
        if (nameInput) nameInput.style.borderColor = '#c33';
        return;
    }

    if (newName.length > 15)
    {
        if (errorDiv) {
            errorDiv.textContent = 'Gruppenname darf maximal 15 Zeichen haben';
            errorDiv.classList.remove('hidden');
        }
        if (nameInput) nameInput.style.borderColor = '#c33';
        return;
    }

    if (nameInput) nameInput.style.borderColor = '';

    const saveBtn = nameInput ? nameInput.parentElement.querySelector('button') : null;
    if (saveBtn) saveBtn.disabled = true;

    const formData = new FormData();
    formData.append('chat_id', currentManageGroupId);
    formData.append('chat_name', newName);

    fetch('/src/components/update_group_name.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data && data.success)
            {
                const updatedName = data.chat_name || newName;

                const titleEl = document.getElementById('manageGroupTitle');

                if (currentChatId === currentManageGroupId)
                {
                    currentChat = updatedName;
                    const currentNameEl = document.getElementById('currentChatName');
                    if (currentNameEl) currentNameEl.textContent = updatedName;
                }

                const li = document.querySelector('#chatList .chat-item[data-chat-id="' + currentManageGroupId + '"]');
                if (li)
                {
                    li.setAttribute('data-chat-name', updatedName);
                    li.setAttribute('data-chat-type', 'group');
                    li.innerHTML = '<span class="chat-icon">👥 | </span><span class="chat-name">' + escapeHtml(updatedName) + '</span>';
                }

                const manageBtn = document.getElementById('manageGroupBtn');
                if (manageBtn)
                {
                    manageBtn.setAttribute('data-chat-name', updatedName);
                }

                if (successDiv)
                {
                    successDiv.textContent = data.message || 'Gruppenname aktualisiert';
                    successDiv.classList.remove('hidden');
                    window.setTimeout(function()
                    {
                        successDiv.classList.add('hidden');
                    }, 3000);
                }

                if (nameInput) nameInput.setAttribute('data-original', updatedName);
            }
            else
            {
                if (errorDiv)
                {
                    errorDiv.textContent = (data && data.message) ? data.message : 'Fehler beim Aktualisieren des Gruppennamens';
                    errorDiv.classList.remove('hidden');
                }
            }
        })
        .catch(function(error)
        {
            console.error('Error updating group name:', error);
            if (errorDiv)
            {
                errorDiv.textContent = 'Fehler beim Aktualisieren des Gruppennamens';
                errorDiv.classList.remove('hidden');
            }
        })
        .finally(function()
        {
            if (saveBtn) saveBtn.disabled = false;
        });
}

function closeManageGroup()
{
    document.getElementById('manageGroupModal').classList.remove('active');
    currentManageGroupId = null;
}

function loadGroupMembers(chatId)
{
    const validChatId = validateId(chatId);
    if (!validChatId) {
        console.error('Ungültige Chat-ID');
        return;
    }

    const membersList = document.getElementById('currentMembersList');
    membersList.innerHTML = '<p style="color: #888;">Lade Mitglieder...</p>';

    const url = buildUrl('/src/components/get_group_members.php', { chat_id: validChatId });

    fetch(url)
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                if (data.members.length === 0)
                {
                    membersList.innerHTML = '<p style="color: #888;">Keine Mitglieder gefunden</p>';
                    return;
                }

                let html = '';
                data.members.forEach(function(member)
                {
                    html += `
                    <div class="member-item" style="display:flex; justify-content:space-between; align-items:center; padding:8px; border-bottom:1px solid #eee;">
                        <div>
                            <strong>${escapeHtml(member.username)}</strong><br>
                            <small style="color: #888;">${escapeHtml(member.email)}</small>
                        </div>
                        <button class="button-secondary"
                        onclick="removeGroupMember(${member.id}, '${escapeHtml(member.username)}', this)"
                        style="padding:4px 8px;">✖️ Entfernen</button>
                        </div>
                    `;
                });

                membersList.innerHTML = html;
            }
            else
            {
                membersList.innerHTML = '<p style="color: #c33;">' + escapeHtml(data.message) + '</p>';
            }
        })
        .catch(function(error)
        {
            console.error('Error loading members:', error);
            membersList.innerHTML = '<p style="color: #c33;">Fehler beim Laden der Mitglieder</p>';
        });
}

function addGroupMember()
{
    const inputField = document.getElementById('addMemberInput');
    const memberInput = inputField.value.trim();
    const errorDiv = document.getElementById('manage-group-error');
    const successDiv = document.getElementById('manage-group-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    if (!memberInput)
    {
        errorDiv.textContent = 'Bitte gib einen Benutzernamen oder E-Mail ein';
        errorDiv.classList.remove('hidden');
        return;
    }

    const validChatId = validateId(currentManageGroupId);
    if (!validChatId)
    {
        errorDiv.textContent = 'Keine Gruppe ausgewählt';
        errorDiv.classList.remove('hidden');
        return;
    }

    const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(memberInput);
    const isValidUsername = /^[A-Za-z0-9_.-]{3,30}$/.test(memberInput);

    if (!isValidEmail && !isValidUsername) {
        errorDiv.textContent = 'Ungültiger Benutzername oder E-Mail';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('chat_id', validChatId);
    formData.append('member_input', memberInput);

    fetch('/src/components/add_group_member.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                successDiv.textContent = data.message;
                successDiv.classList.remove('hidden');
                inputField.value = '';

                loadGroupMembers(currentManageGroupId);

                setTimeout(function()
                {
                    successDiv.classList.add('hidden');
                }, 3000);
            }
            else
            {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(function(error)
        {
            console.error('Error adding member:', error);
            errorDiv.textContent = 'Fehler beim Hinzufügen des Mitglieds';
            errorDiv.classList.remove('hidden');
        });
}

function resetPendingGroupMemberRemoval() {
    if (pendingGroupMemberRemovalTimer) {
        clearTimeout(pendingGroupMemberRemovalTimer);
        pendingGroupMemberRemovalTimer = null;
    }
    if (pendingGroupMemberRemovalBtn) {
        pendingGroupMemberRemovalBtn.textContent = '✖️ Entfernen';
        pendingGroupMemberRemovalBtn.classList.remove('button-danger');
        pendingGroupMemberRemovalBtn.removeAttribute('data-confirming');
    }
    pendingGroupMemberRemovalId = null;
    pendingGroupMemberRemovalBtn = null;
}

function removeGroupMember(memberId, memberName, buttonEl)
{
    if (!buttonEl || pendingGroupMemberRemovalId !== memberId || pendingGroupMemberRemovalBtn !== buttonEl)
    {
        resetPendingGroupMemberRemoval();

        pendingGroupMemberRemovalId = memberId;
        pendingGroupMemberRemovalBtn = buttonEl || null;

        if (pendingGroupMemberRemovalBtn)
        {
            pendingGroupMemberRemovalBtn.textContent = '✔️ Bestätigen';
            pendingGroupMemberRemovalBtn.classList.add('button-danger');
            pendingGroupMemberRemovalBtn.setAttribute('data-confirming', '1');
        }

        pendingGroupMemberRemovalTimer = setTimeout(function()
        {
            resetPendingGroupMemberRemoval();
        }, 3000);

        return;
    }

    const validChatId = validateId(currentManageGroupId);
    const validMemberId = validateId(memberId);

    if (!validChatId || !validMemberId) {
        console.error('Ungültige IDs');
        return;
    }
    resetPendingGroupMemberRemoval();


    const errorDiv = document.getElementById('manage-group-error');
    const successDiv = document.getElementById('manage-group-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    const formData = new FormData();
    formData.append('chat_id', validChatId);
    formData.append('member_id', validMemberId);

    fetch('/src/components/remove_group_member.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                successDiv.textContent = data.message;
                successDiv.classList.remove('hidden');

                loadGroupMembers(currentManageGroupId);

                setTimeout(function()
                {
                    successDiv.classList.add('hidden');
                }, 3000);
            }
            else
            {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(function(error)
        {
            console.error('Error removing member:', error);
            errorDiv.textContent = 'Fehler beim Entfernen des Mitglieds';
            errorDiv.classList.remove('hidden');
        });
}

function toggleImportantPanel()
{
    const panel = document.getElementById('importantPanel');

    if (panel.classList.contains('active'))
    {
        panel.classList.remove('active');
    }
    else
    {
        panel.classList.add('active');
        loadNotes();
    }
}

function addNote()
{
    const input = document.getElementById('newNoteInput');
    const text = input.value.trim();

    if (!text)
    {
        return;
    }

    const validChatId = validateId(currentChatId);
    if (!validChatId)
    {
        showToast('Bitte wähle einen Chat aus', 'error');
        return;
    }

    if (text.length > 1024) {
        showToast('Notiz zu lang (max. 1024 Zeichen)', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('chat_id', validChatId);
    formData.append('content', text);

    fetch('/src/components/add_note.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                input.value = '';
                input.style.height = 'auto';
                loadNotes();
            }
        })
        .catch(function(error)
        {
            console.error('Error adding note:', error);
        });
}

function deleteNote(id)
{
    const validId = validateId(id);
    if (!validId) {
        console.error('Ungültige Notiz-ID');
        return;
    }

    const formData = new FormData();
    formData.append('note_id', validId);

    fetch('/src/components/delete_note.php', {
        method: 'POST',
        body: formData
    })
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                loadNotes();
            }
        })
        .catch(function(error)
        {
            console.error('Error deleting note:', error);
        });
}

function loadNotes()
{
    const validChatId = validateId(currentChatId);
    if (!validChatId)
    {
        document.getElementById('notesList').innerHTML = '<p class="empty-state">Wähle einen Chat aus</p>';
        return;
    }

    const url = buildUrl('/src/components/get_notes.php', { chat_id: validChatId });

    fetch(url)
        .then(function(response)
        {
            if (!response.ok) {
                throw new Error('Server-Fehler: ' + response.status);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server hat kein JSON zurückgegeben');
            }
            return response.json();
        })
        .then(function(data)
        {
            if (data.success)
            {
                const list = document.getElementById('notesList');

                if (data.notes.length === 0)
                {
                    list.innerHTML = '<p class="empty-state">Keine wichtigen Notizen für diesen Chat. Notiere dir etwas! :)</p>';
                    return;
                }

                list.innerHTML = data.notes.map(function(note)
                {
                    const date = new Date(note.created_at);
                    const timeString = date.getHours().toString().padStart(2, '0') + ':' +
                        date.getMinutes().toString().padStart(2, '0');
                    const dateString = date.toLocaleDateString('de-DE');

                    return `
                    <div class="note-item-full">
                        <div class="note-header">
                            <strong class="note-author">${escapeHtml(note.author)}</strong>
                            <span class="note-time">${dateString} ${timeString}</span>
                        </div>
                        <div class="note-content-full">
                            <p class="note-text">${escapeHtml(note.content)}</p>
                        </div>
                        <button class="note-delete-btn-full" onclick="deleteNote(${note.id})" title="Notiz löschen">×</button>
                    </div>
                `;
                }).join('');

                setTimeout(function()
                {
                    list.scrollTop = list.scrollHeight;
                }, 50);
            }
        })
        .catch(function(error)
        {
            console.error('Error loading notes:', error);
        });
}

function isMobile()
{
    return window.innerWidth <= 768;
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

function openEditMessage(messageId, currentContent) {
    const validId = validateId(messageId);
    if (!validId) {
        console.error('Ungültige Message-ID');
        return;
    }

    editingMessageId = validId;
    document.getElementById('editMessageModal').classList.add('active');
    const textarea = document.getElementById('editMessageText');

    if (currentContent) {
        textarea.value = currentContent;
    } else {
        textarea.value = '';
    }

    document.getElementById('edit-error').classList.add('hidden');
    document.getElementById('edit-success').classList.add('hidden');

    setTimeout(function() {
        autoResizeTextarea(textarea);
        textarea.focus();
    }, 100);
}

function closeEditMessage() {
    document.getElementById('editMessageModal').classList.remove('active');
    document.getElementById('editMessageText').value = '';
    editingMessageId = null;
}

function saveEditedMessage() {
    const newContent = document.getElementById('editMessageText').value.trim();
    const errorDiv = document.getElementById('edit-error');
    const successDiv = document.getElementById('edit-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    if (!newContent) {
        errorDiv.textContent = 'Nachricht darf nicht leer sein';
        errorDiv.classList.remove('hidden');
        return;
    }

    if (newContent.length > 2048) {
        errorDiv.textContent = 'Nachricht zu lang (max. 2048 Zeichen)';
        errorDiv.classList.remove('hidden');
        return;
    }

    const validId = validateId(editingMessageId);
    if (!validId) {
        errorDiv.textContent = 'Ungültige Message-ID';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('message_id', validId);
    formData.append('content', newContent);

    fetch('/src/components/edit_message.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                successDiv.textContent = 'Nachricht aktualisiert!';
                successDiv.classList.remove('hidden');

                setTimeout(() => {
                    closeEditMessage();
                    loadMessages(currentChatId, true);
                }, 1000);
            } else {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorDiv.textContent = 'Fehler beim Bearbeiten';
            errorDiv.classList.remove('hidden');
        });
}

function openDeleteMessage(messageId) {
    const validId = validateId(messageId);
    if (!validId) {
        console.error('Ungültige Message-ID');
        return;
    }

    deletingMessageId = validId;
    document.getElementById('deleteMessageModal').classList.add('active');
    document.getElementById('delete-error').classList.add('hidden');
}

function closeDeleteMessage() {
    document.getElementById('deleteMessageModal').classList.remove('active');
    deletingMessageId = null;
}

function confirmDeleteMessage() {
    if (!deletingMessageId) {
        return;
    }

    const validId = validateId(deletingMessageId);
    if (!validId) {
        console.error('Ungültige Message-ID');
        return;
    }

    const formData = new FormData();
    formData.append('message_id', validId);

    fetch('/src/components/delete_message.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeDeleteMessage();
                loadMessages(currentChatId, true);
                showToast('Nachricht gelöscht', 'success');
            } else {
                const errorDiv = document.getElementById('delete-error');
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorDiv = document.getElementById('delete-error');
            errorDiv.textContent = 'Fehler beim Löschen der Nachricht';
            errorDiv.classList.remove('hidden');
        });
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
    }, 100);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 2000);
}

let currentInviteCode = null;

function loadInviteCode(chatId)
{
    const validChatId = validateId(chatId);
    if (!validChatId) {
        console.error('Ungültige Chat-ID');
        return;
    }

    const codeDisplay = document.getElementById('inviteCodeDisplay');
    const copyBtn = document.getElementById('copyInviteCodeBtn');

    if (!codeDisplay) return;

    codeDisplay.value = 'Lade...';
    if (copyBtn) copyBtn.disabled = true;

    const url = buildUrl('/src/components/get_invite_code.php', { chat_id: validChatId });

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.invite_code) {
                currentInviteCode = data.invite_code;
                codeDisplay.value = data.invite_code;
                if (copyBtn) copyBtn.disabled = false;
            } else {
                codeDisplay.value = 'Kein Code';
                currentInviteCode = null;
                if (copyBtn) copyBtn.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            codeDisplay.value = 'Fehler';
            currentInviteCode = null;
            if (copyBtn) copyBtn.disabled = true;
        });
}

function copyInviteCodeToClipboard()
{
    if (!currentInviteCode) {
        showToast('Kein Code verfügbar', 'error');
        return;
    }

    const copyBtn = document.getElementById('copyInviteCodeBtn');

    if (navigator.clipboard) {
        navigator.clipboard.writeText(currentInviteCode).then(() => {
            showToast('Code kopiert: ' + currentInviteCode, 'success');

            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '✔️ Code kopiert!';

            setTimeout(() => {
                copyBtn.innerHTML = originalText;
                copyBtn.style.background = '';
            }, 2000);
        }).catch(() => {
            fallbackCopy();
        });
    } else {
        fallbackCopy();
    }

    function fallbackCopy() {
        const textArea = document.createElement('textarea');
        textArea.value = currentInviteCode;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showToast('Code kopiert: ' + currentInviteCode, 'success');

            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '✔️ Code kopiert!';

            setTimeout(() => {
                copyBtn.innerHTML = originalText;
                copyBtn.style.background = '';
            }, 2000);
        } catch (err) {
            alert('Code: ' + currentInviteCode);
        }
        document.body.removeChild(textArea);
    }
}

document.addEventListener('DOMContentLoaded', function()
{
    loadChats();

    const modals = ['settingsModal', 'addContactModal', 'addGroupModal', 'manageGroupModal', 'editUsernameModal', 'editEmailModal', 'editPasswordModal', 'editMessageModal', 'deleteMessageModal'];
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
                    if(modalId === 'manageGroupModal') closeManageGroup();
                    if(modalId === 'editUsernameModal') closeEditUsername();
                    if(modalId === 'editEmailModal') closeEditEmail();
                    if(modalId === 'editPasswordModal') closeEditPassword();
                    if(modalId === 'editMessageModal') closeEditMessage();
                    if(modalId === 'deleteMessageModal') closeDeleteMessage();
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

    const editMessageText = document.getElementById('editMessageText');
    if (editMessageText)
    {
        editMessageText.addEventListener('input', function()
        {
            autoResizeTextarea(this);
        });

        editMessageText.addEventListener('keydown', function(e)
        {
            if (e.key === 'Enter' && !e.shiftKey)
            {
                e.preventDefault();
                saveEditedMessage();
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-message-btn')) {
            const messageId = parseInt(e.target.getAttribute('data-message-id'));
            const contentBase64 = e.target.getAttribute('data-content');
            const content = decodeURIComponent(escape(atob(contentBase64)));
            openEditMessage(messageId, content);
        }

        if (e.target.classList.contains('delete-message-btn')) {
            const messageId = parseInt(e.target.getAttribute('data-message-id'));
            openDeleteMessage(messageId);
        }
    });
});

window.addEventListener('beforeunload', function()
{
    stopAutoReload();
});