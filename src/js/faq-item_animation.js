document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');

    // --- TEIL 1: Eingangs-Animation beim Laden der Seite ---
    faqItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';

        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
            item.classList.add('animated');
        }, 500 + (150 * index));
    });

    // --- TEIL 2: Akkordeon-Animation (Öffnen & Schließen) ---
    faqItems.forEach(item => {
        const summary = item.querySelector('summary');
        const content = item.querySelector('.faq-animator');

        // WICHTIG: CSS Transition muss hier gesetzt sein, oder im CSS File
        content.style.transition = 'height 0.4s ease';

        summary.addEventListener('click', (e) => {
            // Standard-Verhalten (sofortiges Öffnen/Schließen) verhindern
            e.preventDefault();

            // Überprüfen, ob das Item gerade geöffnet wird oder geschlossen
            if (item.hasAttribute('open')) {
                // --- SCHLIESSEN ---
                // 1. Setze exakte Höhe (damit Transition von X px zu 0 px funktioniert)
                content.style.height = content.scrollHeight + 'px';

                // 2. Kurzer Timeout, damit der Browser die Höhe registriert
                requestAnimationFrame(() => {
                    content.style.height = '0px';
                });

                // 3. Warten bis Transition vorbei ist, dann 'open' entfernen
                content.addEventListener('transitionend', function onEnd() {
                    item.removeAttribute('open');
                    content.removeEventListener('transitionend', onEnd);
                }, { once: true });

            } else {
                // --- ÖFFNEN ---
                // 1. Attribut setzen, damit Inhalt gerendert wird
                item.setAttribute('open', '');

                // 2. Start-Höhe auf 0 setzen
                content.style.height = '0px';

                // 3. Berechnung der Zielhöhe
                const height = content.scrollHeight;

                // 4. Animation starten
                requestAnimationFrame(() => {
                    content.style.height = height + 'px';
                });

                // 5. Nach Animation auf 'auto' setzen (für Responsiveness bei Fenstergrößenänderung)
                content.addEventListener('transitionend', function onEnd() {
                    content.style.height = 'auto';
                    content.removeEventListener('transitionend', onEnd);
                }, { once: true });
            }
        });
    });
});