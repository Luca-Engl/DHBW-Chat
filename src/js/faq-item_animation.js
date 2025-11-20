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
            item.classList.add('animated');
        }, 500 + (150 * index));
    });

    // Animation für das Aus- und Einklappen
    faqItems.forEach(item => {
        const animator = item.querySelector('.faq-animator');
        const content = item.querySelector('.faq-content');

        // Setze initiale Höhe auf 0
        animator.style.height = '0px';
        animator.style.overflow = 'hidden';
        animator.style.transition = 'height 0.4s ease';

        item.addEventListener('toggle', function() {
            if (item.open) {
                // Öffnen
                const contentHeight = content.offsetHeight;
                animator.style.height = contentHeight + 'px';
            } else {
                // Schließen
                animator.style.height = '0px';
            }
        });
    });
});