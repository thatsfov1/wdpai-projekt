const profileDropdown = document.querySelector('.profile-dropdown');

if (profileDropdown) {
    const trigger = profileDropdown.querySelector('.profile-trigger');

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.classList.toggle('active');
    });

    document.addEventListener('click', (e) => {
        if (!profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('active');
        }
    });
}

const burgerMenu = document.querySelector('.burger-menu');
const burgerDropdown = document.querySelector('.burger-dropdown');

if (burgerMenu && burgerDropdown) {
    burgerMenu.addEventListener('click', (e) => {
        e.stopPropagation();
        burgerMenu.classList.toggle('active');
        burgerDropdown.classList.toggle('active');
    });

    document.addEventListener('click', (e) => {
        if (!burgerMenu.contains(e.target) && !burgerDropdown.contains(e.target)) {
            burgerMenu.classList.remove('active');
            burgerDropdown.classList.remove('active');
        }
    });

    const burgerLinks = burgerDropdown.querySelectorAll('a');
    for (let i = 0; i < burgerLinks.length; i++) {
        burgerLinks[i].addEventListener('click', () => {
            burgerMenu.classList.remove('active');
            burgerDropdown.classList.remove('active');
        });
    }
}

const anchors = document.querySelectorAll('a[href^="#"]');
for (let i = 0; i < anchors.length; i++) {
    anchors[i].addEventListener('click', (e) => {
        const targetId = anchors[i].getAttribute('href');
        if (targetId === '#') return;

        const target = document.querySelector(targetId);
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
}

window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        if (burgerMenu) burgerMenu.classList.remove('active');
        if (burgerDropdown) burgerDropdown.classList.remove('active');
    }
});

