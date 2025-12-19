(function () {
    const box = document.getElementById('pageError');
    const text = document.getElementById('pageErrorText');

    let timer = null;

    function hide() {
        if (!box) return;
        box.classList.add('is-hidden');
    }

    function show(message) {
        if (!box || !text) return;
        text.textContent = message;
        box.classList.remove('is-hidden');

        if (timer) window.clearTimeout(timer);
        timer = window.setTimeout(hide, 5000);
    }

    window.hidePageError = hide;
    window.showPageError = show;

    if (text && text.textContent.trim() !== '') {
        timer = window.setTimeout(hide, 5000);
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            const username = (document.getElementById('username')?.value || '').trim();
            const password = document.getElementById('password')?.value || '';

            if (username === '' || password === '') {
                e.preventDefault();
                show('Bitte fülle alle Felder aus.');
                return;
            }
            if (!/^[A-Za-z0-9]+$/.test(username)) {
                e.preventDefault();
                show('Benutzername darf nur Buchstaben und Zahlen enthalten.');
                return;
            }
            if (username.length > 30) {
                e.preventDefault();
                show('Benutzername darf maximal 30 Zeichen haben.');
                return;
            }
            if (password.length < 6 || password.length > 30) {
                e.preventDefault();
                show('Passwort muss zwischen 6 und 30 Zeichen lang sein.');
                return;
            }
        });
    }

    // Auto-Großbuchstaben beim Code eingeben
    const groupcodeInput = document.getElementById('groupcode');
    if (groupcodeInput) {
        groupcodeInput.addEventListener('input', function(e) {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }

    const guestForm = document.getElementById('guestForm');
    if (guestForm) {
        guestForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const code = (groupcodeInput?.value || '').trim();

            if (code === '') {
                show('Bitte gib einen Gruppencode ein.');
                return;
            }
            if (code.length !== 6) {
                show('Der Gruppencode muss genau 6 Zeichen lang sein.');
                return;
            }

            const submitBtn = guestForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Prüfe Code...';

            fetch('/src/components/validate_guest_code.php?code=' + encodeURIComponent(code))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'chat.php?groupcode=' + code;
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                        show(data.message || 'Ungültiger Code');
                    }
                })
                .catch(error => {
                    console.error('Validierung fehlgeschlagen:', error);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    show('Fehler bei der Validierung. Bitte versuche es erneut.');
                });
        });
    }
})();