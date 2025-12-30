document.addEventListener('DOMContentLoaded', function () {
    const profileDropdown = document.querySelector('.profile-dropdown');

    if (profileDropdown) {
        const trigger = profileDropdown.querySelector('.profile-trigger');

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
            if (!profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });
    }

    const burgerMenu = document.querySelector('.burger-menu');
    const burgerDropdown = document.querySelector('.burger-dropdown');

    if (burgerMenu && burgerDropdown) {
        burgerMenu.addEventListener('click', function (e) {
            e.stopPropagation();
            burgerMenu.classList.toggle('active');
            burgerDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
            if (!burgerMenu.contains(e.target) && !burgerDropdown.contains(e.target)) {
                burgerMenu.classList.remove('active');
                burgerDropdown.classList.remove('active');
            }
        });

        burgerDropdown.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function () {
                burgerMenu.classList.remove('active');
                burgerDropdown.classList.remove('active');
            });
        });
    }

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
            if (burgerMenu) burgerMenu.classList.remove('active');
            if (burgerDropdown) burgerDropdown.classList.remove('active');
        }
    });
});

