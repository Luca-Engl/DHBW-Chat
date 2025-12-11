let currentChatId = null;
let currentChat = 'Globalchat';
let messageCheckInterval = null;
let currentManageGroupId = null;

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

function loadChats()
{
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
                    li.textContent = chat.chat_name;
                    li.setAttribute('data-chat-id', chat.id);
                    li.setAttribute('data-chat-name', chat.chat_name);
                    li.setAttribute('data-chat-type', chat.chat_type);

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
    if (typeof isAutoReload === 'undefined')
    {
        isAutoReload = false;
    }

    fetch('/src/components/get_messages.php?chat_id=' + chatId)
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
                    messagesHtml = '<p style="text-align: center; color: #888; padding: 20px;">Noch keine Nachrichten. Schreibe die erste!</p>';
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

                        messagesHtml += `
                        <section class="message ${messageClass}">
                            <span class="timestamp">${timeString}</span>
                            <section class="bubble">
                                ${senderInfo}${escapeHtml(msg.content)}
                            </section>
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
                        <textarea id="chatmessage" name="chatmessage" rows="2"
                                  placeholder="Nachricht eingeben..."
                                  inputmode="text" aria-label="Nachricht eingeben"></textarea>
                        <button type="submit" class="style-bold">Senden</button>
                    </form>
                `;

                    chatHistory.innerHTML = messagesHtml;

                    const chatForm = document.getElementById('chatForm');
                    if (chatForm)
                    {
                        chatForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            sendMessage(e);
                        });
                    }

                    const chatInput = document.getElementById('chatmessage');
                    if (chatInput)
                    {
                        chatInput.addEventListener('keydown', function(e)
                        {
                            if (e.key === 'Enter' && !e.shiftKey)
                            {
                                e.preventDefault();
                                sendMessage(e);
                            }
                        });
                    }
                }

                if (wasAtBottom || !isAutoReload)
                {
                    scrollToBottom();
                }
            }
            else
            {
                console.error('Load messages failed:', data.message);
            }
        })
        .catch(function(error)
        {
            console.error('Error loading messages:', error);
        });
}

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

    if (!currentChatId)
    {
        return;
    }

    const inputField = document.getElementById("chatmessage");
    if (!inputField) {
        console.error('Input field not found!');
        return;
    }

    const text = inputField.value.trim();

    if (text === "")
    {
        return;
    }

    const formData = new FormData();
    formData.append('chat_id', currentChatId);
    formData.append('content', text);

    fetch('/src/components/send_message.php', {
        method: 'POST',
        body: formData
    })
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
            if (data.success)
            {
                inputField.value = "";
                inputField.style.height = 'auto';
                loadMessages(currentChatId);
            }
        })
        .catch(function(error)
        {
            console.error('Error sending message:', error);
        });
}

function startAutoReload()
{
    if (messageCheckInterval)
    {
        clearInterval(messageCheckInterval);
    }

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
    const inputField = document.getElementById('contactInput');
    if (inputField) {
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
    if(inputField) {
        inputField.value = '';
        inputField.style.borderColor = '';
    }
    const errorDiv = document.getElementById('contact-error');
    const successDiv = document.getElementById('contact-success');
    if (errorDiv) errorDiv.classList.add('hidden');
    if (successDiv) successDiv.classList.add('hidden');
}

function addContact()
{
    const inputField = document.getElementById('contactInput');
    const input = inputField ? inputField.value.trim() : '';
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
            <button class="member-remove button-secondary" onclick="removeMember('${escapeHtml(member)}')" title="Entfernen" style="padding:2px 8px; font-size:0.8rem;">×</button>
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
                successDiv.textContent = 'Gruppe "' + data.chat_name + '" erstellt!';
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
    document.getElementById('manageGroupTitle').textContent = 'Gruppe verwalten: ' + chatName;
    document.getElementById('addMemberInput').value = '';
    document.getElementById('addMemberInput').style.borderColor = '';

    const errorDiv = document.getElementById('manage-group-error');
    const successDiv = document.getElementById('manage-group-success');
    if (errorDiv) errorDiv.classList.add('hidden');
    if (successDiv) successDiv.classList.add('hidden');

    document.getElementById('manageGroupModal').classList.add('active');
    loadGroupMembers(chatId);
}

function closeManageGroup()
{
    document.getElementById('manageGroupModal').classList.remove('active');
    currentManageGroupId = null;
}

function loadGroupMembers(chatId)
{
    const membersList = document.getElementById('currentMembersList');
    membersList.innerHTML = '<p style="color: #888;">Lade Mitglieder...</p>';

    fetch('/src/components/get_group_members.php?chat_id=' + chatId)
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
                    membersList.innerHTML = '<p style="color: #888;">Keine Mitglieder</p>';
                    return;
                }

                let html = '';
                data.members.forEach(function(member)
                {
                    html += `
                    <div class="member-item" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; padding:8px; background:rgba(255,255,255,0.05); border-radius:8px;">
                        <div style="flex:1;">
                            <strong>${escapeHtml(member.username)}</strong><br>
                            <small style="color:#888;">${escapeHtml(member.email)}</small>
                        </div>
                        <button class="member-remove button-secondary" onclick="removeGroupMember(${member.id})" title="Entfernen" style="padding:4px 10px; font-size:0.9rem;">Entfernen</button>
                    </div>
                    `;
                });

                membersList.innerHTML = html;
            }
            else
            {
                membersList.innerHTML = '<p style="color: #c33;">Fehler beim Laden</p>';
            }
        })
        .catch(function(error)
        {
            console.error('Error loading members:', error);
            membersList.innerHTML = '<p style="color: #c33;">Fehler beim Laden</p>';
        });
}

function addGroupMember()
{
    if (!currentManageGroupId)
    {
        return;
    }

    const inputField = document.getElementById('addMemberInput');
    const input = inputField.value.trim();
    const errorDiv = document.getElementById('manage-group-error');
    const successDiv = document.getElementById('manage-group-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');
    inputField.style.borderColor = '';

    if (input === '')
    {
        errorDiv.textContent = 'Bitte gib einen Benutzernamen oder E-Mail ein';
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
    formData.append('chat_id', currentManageGroupId);
    formData.append('member_input', input);

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
                inputField.style.borderColor = '';
                loadGroupMembers(currentManageGroupId);
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
            errorDiv.textContent = 'Fehler beim Hinzufügen';
            errorDiv.classList.remove('hidden');
        });
}

function removeGroupMember(memberId)
{
    if (!currentManageGroupId)
    {
        return;
    }

    const errorDiv = document.getElementById('manage-group-error');
    const successDiv = document.getElementById('manage-group-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    const formData = new FormData();
    formData.append('chat_id', currentManageGroupId);
    formData.append('member_id', memberId);

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
                loadChats();
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
            errorDiv.textContent = 'Fehler beim Entfernen';
            errorDiv.classList.remove('hidden');
        });
}

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

    if (!currentChatId)
    {
        return;
    }

    const formData = new FormData();
    formData.append('chat_id', currentChatId);
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
    const formData = new FormData();
    formData.append('note_id', id);

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
    if (!currentChatId)
    {
        document.getElementById('notesList').innerHTML = '<p class="empty-state">Wähle einen Chat aus</p>';
        return;
    }

    fetch('/src/components/get_notes.php?chat_id=' + currentChatId)
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
                    list.innerHTML = '<p class="empty-state">Noch keine Notizen in diesem Chat</p>';
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
                        <button class="note-delete-btn-full" onclick="deleteNote(${note.id})" title="Löschen">×</button>
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

document.addEventListener('DOMContentLoaded', function()
{
    loadChats();

    const modals = ['settingsModal', 'addContactModal', 'addGroupModal', 'manageGroupModal', 'editUsernameModal', 'editEmailModal', 'editPasswordModal'];
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
});

window.addEventListener('beforeunload', function()
{
    stopAutoReload();
});