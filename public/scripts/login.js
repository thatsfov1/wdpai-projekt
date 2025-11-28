const toggleWrapper = document.querySelector('.eye-icon');
const passwordInput = document.querySelector('#password');
const eyeOpen = document.querySelector('.fa-eye');
const eyeClosed = document.querySelector('.fa-eye-slash');

toggleWrapper.addEventListener('click', function () {
    const isPassword = passwordInput.getAttribute('type') === 'password';

    if (isPassword) {
        passwordInput.setAttribute('type', 'text');
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
    } else {
        passwordInput.setAttribute('type', 'password');
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
    }
});