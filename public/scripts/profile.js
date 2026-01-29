const phoneInput = document.getElementById('phone');
const form = document.querySelector('.profile-form');

if (phoneInput && form) {
    phoneInput.addEventListener('input', () => {
        const value = this.value.trim();
        const phoneRegex = /^\+48\s?\d{3}\s?\d{3}\s?\d{3}$|^\+48\d{9}$/;

        if (value && !phoneRegex.test(value)) {
            this.setCustomValidity('Numer telefonu musi zaczynać się od +48 i mieć 9 cyfr (np. +48 123 456 789)');
        } else {
            this.setCustomValidity('');
        }
    });

    phoneInput.setAttribute('pattern', '\\+48\\s?\\d{3}\\s?\\d{3}\\s?\\d{3}|\\+48\\d{9}');
}