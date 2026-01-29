const passwordWrappers = document.querySelectorAll('.password-wrapper');

for (let i = 0; i < passwordWrappers.length; i++) {
    const wrapper = passwordWrappers[i];
    const toggleWrapper = wrapper.querySelector('.eye-icon');
    const passwordInput = wrapper.querySelector('input[type="password"], input[type="text"]');
    const eyeOpen = wrapper.querySelector('.fa-eye');
    const eyeClosed = wrapper.querySelector('.fa-eye-slash');

    if (!toggleWrapper || !passwordInput || !eyeOpen || !eyeClosed) continue;

    toggleWrapper.addEventListener('click', () => {
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
}
