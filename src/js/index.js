document.addEventListener('DOMContentLoaded', () => {
    // Observer für Feature-Boxen Animation
    const featureObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList. add('visible');
                setTimeout(() => {
                    entry. target.classList.add('animation-done');
                }, 1500);
            }
        });
    }, { threshold: 0.2 });

    // Feature-Boxen beobachten
    const featureBoxes = document.querySelectorAll('.feature-box');
    if (featureBoxes.length > 0) {
        featureBoxes. forEach(box => featureObserver.observe(box));
    }

    // Observer für D-Feature-Text Boxen Animation
    const dFeatureObserver = new IntersectionObserver((entries) => {
        entries. forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.2,
        rootMargin: '0px 0px -50px 0px'
    });

    // D-Feature-Text Boxen beobachten
    const dFeatureBoxes = document.querySelectorAll('.d-feature-text');
    if (dFeatureBoxes.length > 0) {
        dFeatureBoxes.forEach(box => dFeatureObserver.observe(box));
    }
});