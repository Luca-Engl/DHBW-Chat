// Privacy-Boxen nacheinander einfliegen lassen
document.addEventListener('DOMContentLoaded', function() {
    const privacyBoxes = document.querySelectorAll('.privacy-box');

    privacyBoxes. forEach((box, index) => {
        box.style.transition = 'opacity 0.6s ease, transform 0.6s ease';

        setTimeout(() => {
            box.style.opacity = '1';
            box. style.transform = '';
            box.classList.add('animated');
        }, 500 + (150 * index));
    });
});