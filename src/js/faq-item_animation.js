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

    // Animation für das Ausklappen
    faqItems.forEach(item => {
        const animator = item.querySelector('.faq-animator');

        // Setze initiale Styles
        animator.style.overflow = 'hidden';
        animator.style.transition = 'height 0.4s ease';
        animator.style.height = '0px';

        item.addEventListener('toggle', function() {
            if (item.open) {
                // ÖFFNEN: Messe die natürliche Höhe
                animator.style.height = 'auto';
                const height = animator.offsetHeight;
                animator.style.height = '0px';

                // Trigger Reflow
                void animator.offsetHeight;

                // Dann animiere zur Zielhöhe
                animator.style.height = height + 'px';
            }
        });

        // Setze Höhe auf auto nach Animation, damit Inhalt flexibel bleibt
        animator.addEventListener('transitionend', function() {
            if (item.open) {
                animator.style.height = 'auto';
            }
        });
    });
});