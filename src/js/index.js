// Observer für Feature-Boxen (die 4 oben)
const featureObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            setTimeout(() => {
                entry.target.classList.add('animation-done');
            }, 1500);
        }
    });
}, { threshold: 0.2 });

document.querySelectorAll('.feature-box').forEach(box => featureObserver.observe(box));

document.addEventListener('DOMContentLoaded', () => {
    // Feature-Boxen
    const featureBoxes = document.querySelectorAll('.feature-box');

    // D-Feature-Text Boxen (die großen)
    const dFeatureBoxes = document.querySelectorAll('.d-feature-text');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.2,
        rootMargin: '0px 0px -50px 0px'
    });

    // Beide beobachten
    featureBoxes.forEach(box => observer.observe(box));
    dFeatureBoxes.forEach(box => observer.observe(box));
});
const responsiveNavHamburger = document.getElementById('responsiveNavHamburger');
const responsiveNavMobileMenu = document.getElementById('responsiveNavMobileMenu');

responsiveNavHamburger.addEventListener('click', () => {
    responsiveNavHamburger.classList.toggle('active');
    responsiveNavMobileMenu.classList.toggle('active');
});