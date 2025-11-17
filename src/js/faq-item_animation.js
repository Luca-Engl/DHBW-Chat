// Animation für FAQ-Items beim Laden der Seite
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach((item, index) => {
        // Setze Anfangszustand
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';

        // Starte Animation nacheinander mit Verzögerung
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
            item.classList.add('animated'); // Markiere als animiert
        }, 500 + (150 * index)); // 500ms Initialverzögerung + 150ms zwischen den Items
    });
});