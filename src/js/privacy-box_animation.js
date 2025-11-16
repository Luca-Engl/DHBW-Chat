// Animation für Privacy-Boxen beim Laden der Seite
document.addEventListener('DOMContentLoaded', function() {
    const privacyBoxes = document.querySelectorAll('.privacy-box');

    privacyBoxes.forEach((box, index) => {
        // Setze Transition (Anfangszustand ist bereits im CSS)
        box.style.transition = 'opacity 0.6s ease, transform 0.6s ease';

        // Starte Animation nacheinander mit Verzögerung
        setTimeout(() => {
            box.style.opacity = '1';
            box.style.transform = '';  // Entferne inline transform, damit CSS Hover funktioniert
            box.classList.add('animated'); // Markiere als animiert
        }, 500 + (150 * index)); // 500ms Initialverzögerung + 150ms zwischen den Boxen
    });
});