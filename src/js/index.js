document.addEventListener('DOMContentLoaded', () => {
    // Feature-Boxen Observer (die 4 oben mit Animation)
    const featureObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target. classList.add('visible');
                setTimeout(() => {
                    entry. target.classList.add('animation-done');
                }, 1500);
            }
        });
    }, { threshold: 0.2 });

    // Feature-Boxen beobachten (nur wenn vorhanden)
    const featureBoxes = document.querySelectorAll('.feature-box');
    if (featureBoxes.length > 0) {
        featureBoxes. forEach(box => featureObserver.observe(box));
    }

    // D-Feature-Text Boxen Observer (die großen)
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

    // D-Feature-Text Boxen beobachten (nur wenn vorhanden)
    const dFeatureBoxes = document.querySelectorAll('.d-feature-text');
    if (dFeatureBoxes.length > 0) {
        dFeatureBoxes.forEach(box => dFeatureObserver.observe(box));
    }
});