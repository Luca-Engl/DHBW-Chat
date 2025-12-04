var coursesByFaculty = {
    'T': [
        {value: 'INF', name: 'Angewandte Informatik'},
        {value: 'ELT', name: 'Elektrotechnik'},
        {value: 'MB', name: 'Maschinenbau'},
        {value: 'MT', name: 'Mechatronik'},
        {value: 'WIW', name: 'Wirtschaftsingenieurwesen'}
    ],
    'W': [
        {value: 'BWL', name: 'BWL - Industrie'},
        {value: 'BWL-BANK', name: 'BWL - Bank'},
        {value: 'BWL-DLM', name: 'BWL - Dienstleistungsmanagement'},
        {value: 'WINFO', name: 'Wirtschaftsinformatik'}
    ],
    'S': [
        {value: 'SOZARB', name: 'Soziale Arbeit'},
        {value: 'SOZMAN', name: 'Sozialmanagement'},
        {value: 'SOZPAED', name: 'Sozialpädagogik'}
    ],
    'G': [
        {value: 'PFLEGE', name: 'Angewandte Gesundheitswissenschaften'},
        {value: 'PHYSIO', name: 'Physiotherapie'},
        {value: 'INTERPFL', name: 'Interprofessionelle Gesundheitsversorgung'}
    ],
    'A': [
        {value: 'AGRAR', name: 'Agrarwirtschaft'},
        {value: 'SONST', name: 'Sonstiges'}
    ]
};

function updateCourses() {
    var faculty = document.getElementById('faculty').value;
    var cursusSelect = document.getElementById('cursus');
    var yearSelect = document.getElementById('year');

    cursusSelect.innerHTML = '<option value="">Studiengang wählen ...</option>';
    yearSelect.innerHTML = '<option value="">Erst Studiengang wählen ...</option>';
    yearSelect.disabled = true;

    if (faculty && coursesByFaculty[faculty]) {
        cursusSelect.disabled = false;

        var courses = coursesByFaculty[faculty];
        for (var i = 0; i < courses.length; i++) {
            var option = document.createElement('option');
            option.value = courses[i].value;
            option.textContent = courses[i].name;
            cursusSelect.appendChild(option);
        }
    } else {
        cursusSelect.disabled = true;
        cursusSelect.innerHTML = '<option value="">Erst Fakultät wählen ...</option>';
    }
}

function updateYear() {
    var cursus = document.getElementById('cursus').value;
    var yearSelect = document.getElementById('year');

    if (cursus) {
        yearSelect.disabled = false;
        yearSelect.innerHTML = '<option value="">Jahrgang wählen ...</option>' +
            '<option value="2025">2025</option>' +
            '<option value="2024">2024</option>' +
            '<option value="2023">2023</option>' +
            '<option value="2022">2022</option>' +
            '<option value="2021">2021</option>' +
            '<option value="2020">2020</option>';
    } else {
        yearSelect.disabled = true;
        yearSelect.innerHTML = '<option value="">Erst Studiengang wählen ...</option>';
    }
}

function showStep2() {
    var username = document.getElementById('username').value.trim();
    var email = document.getElementById('displayname').value.trim();
    var faculty = document.getElementById('faculty').value;
    var cursus = document.getElementById('cursus').value;
    var year = document.getElementById('year').value;

    if (!username || !email) {
        alert('Bitte fülle Benutzername und E-Mail aus!');
        return;
    }

    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert('Bitte gib eine gültige E-Mail-Adresse ein!');
        return;
    }

    var usernamePattern = /^[A-Za-z0-9]+$/;
    if (!usernamePattern.test(username)) {
        alert('Benutzername darf nur Buchstaben und Zahlen enthalten!');
        return;
    }

    if (!faculty) {
        alert('Bitte wähle eine Fakultät!');
        return;
    }

    if (!cursus) {
        alert('Bitte wähle einen Studiengang!');
        return;
    }

    if (!year) {
        alert('Bitte wähle einen Jahrgang!');
        return;
    }

    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.remove('hidden');

    document.getElementById('password').required = true;
    document.getElementById('password_rep').required = true;
}

function showStep1() {
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step1').classList.remove('hidden');

    document.getElementById('password').required = false;
    document.getElementById('password_rep').required = false;
}