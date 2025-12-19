document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document. querySelectorAll('.faq-item');

    // FAQ-Items nacheinander einfliegen lassen
    faqItems.forEach((item, index) => {
        item.style.opacity = '0';
        item. style.transform = 'translateY(20px)';
        item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';

        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
            item.classList.add('animated');
        }, 500 + (150 * index));
    });

    // Akkordeon-Funktionalität für Öffnen und Schließen
    faqItems.forEach(item => {
        const summary = item.querySelector('summary');
        const content = item.querySelector('.faq-animator');

        content.style.transition = 'height 0.4s ease';

        summary.addEventListener('click', (e) => {
            e.preventDefault();

            if (item.hasAttribute('open')) {
                content.style.height = content.scrollHeight + 'px';
                requestAnimationFrame(() => {
                    content.style.height = '0px';
                });

                content.addEventListener('transitionend', function onEnd() {
                    item.removeAttribute('open');
                    content.removeEventListener('transitionend', onEnd);
                }, { once: true });

            } else {
                item.setAttribute('open', '');
                content.style.height = '0px';
                const height = content.scrollHeight;

                requestAnimationFrame(() => {
                    content.style.height = height + 'px';
                });

                content.addEventListener('transitionend', function onEnd() {
                    content.style.height = 'auto';
                    content.removeEventListener('transitionend', onEnd);
                }, { once: true });
            }
        });
    });
});