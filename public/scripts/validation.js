document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    if (!form) return;

    const emailInput = form.querySelector('input[name="email"]');
    const passwordInput = form.querySelector('input[name="password"]');
    const confirmedPasswordInput = form.querySelector('input[name="password2"]');
    const nameInput = form.querySelector('input[name="name"]');

    let emailTimer = null;
    let passwordTimer = null;
    let confirmPasswordTimer = null;
    let nameTimer = null;

    const DEBOUNCE_DELAY = 1000;

    const isEmail = (email) => {
        return /\S+@\S+\.\S+/.test(email);
    }

    const isStrongPassword = (password) => {
        return password.length >= 6;
    }

    const arePasswordsSame = (password, confirmedPassword) => {
        return password === confirmedPassword && confirmedPassword.length > 0;
    }

    const isValidName = (name) => {
        if (name.length < 2) {
            return false;
        }
        const nameRegex = /^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ\s\-]+$/;
        return nameRegex.test(name);
    }

    const markValidation = (element, condition) => {
        if (!element) return;

        if (!condition) {
            element.classList.add('no-valid');
            element.classList.remove('valid');
        } else {
            element.classList.remove('no-valid');
            element.classList.add('valid');
        }
    }

    const showError = (element, message) => {
        if (!element) return;

        removeError(element);

        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error';
        errorDiv.textContent = message;

        const wrapper = element.closest('.password-wrapper') || element;
        wrapper.parentNode.insertBefore(errorDiv, wrapper.nextSibling);
    }

    const removeError = (element) => {
        if (!element) return;

        const wrapper = element.closest('.password-wrapper') || element;
        const existingError = wrapper.parentNode.querySelector('.validation-error');
        if (existingError) {
            existingError.remove();
        }
    }

    const validateEmail = () => {
        if (!emailInput) return;

        clearTimeout(emailTimer);
        emailTimer = setTimeout(() => {
            const isValid = isEmail(emailInput.value);
            markValidation(emailInput, isValid);

            if (!isValid && emailInput.value.length > 0) {
                showError(emailInput, 'Wprowadź poprawny adres email');
            } else {
                removeError(emailInput);
            }
        }, DEBOUNCE_DELAY);
    }

    const validatePassword = () => {
        if (!passwordInput) return;

        clearTimeout(passwordTimer);
        passwordTimer = setTimeout(() => {
            const isValid = isStrongPassword(passwordInput.value);
            markValidation(passwordInput, isValid);

            if (!isValid && passwordInput.value.length > 0) {
                showError(passwordInput, 'Hasło musi zawierać minimum 6 znaków');
            } else {
                removeError(passwordInput);
            }

            if (confirmedPasswordInput && confirmedPasswordInput.value.length > 0) {
                validateConfirmPassword();
            }
        }, DEBOUNCE_DELAY);
    }

    const validateConfirmPassword = () => {
        if (!confirmedPasswordInput || !passwordInput) return;

        clearTimeout(confirmPasswordTimer);
        confirmPasswordTimer = setTimeout(() => {
            const isValid = arePasswordsSame(passwordInput.value, confirmedPasswordInput.value);
            markValidation(confirmedPasswordInput, isValid);

            if (!isValid && confirmedPasswordInput.value.length > 0) {
                showError(confirmedPasswordInput, 'Hasła nie są identyczne');
            } else {
                removeError(confirmedPasswordInput);
            }
        }, DEBOUNCE_DELAY);
    }

    const validateName = () => {
        if (!nameInput) return;

        clearTimeout(nameTimer);
        nameTimer = setTimeout(() => {
            const isValid = isValidName(nameInput.value);
            markValidation(nameInput, isValid);

            if (!isValid && nameInput.value.length > 0) {
                if (nameInput.value.length < 2) {
                    showError(nameInput, 'Imię i nazwisko musi zawierać minimum 2 znaki');
                } else {
                    showError(nameInput, 'Dozwolone są tylko litery, spacje i myślniki');
                }
            } else {
                removeError(nameInput);
            }
        }, DEBOUNCE_DELAY);
    }

    if (emailInput) {
        emailInput.addEventListener('keyup', validateEmail);
        emailInput.addEventListener('blur', () => {
            clearTimeout(emailTimer);
            const isValid = isEmail(emailInput.value);
            markValidation(emailInput, isValid);
            if (!isValid && emailInput.value.length > 0) {
                showError(emailInput, 'Wprowadź poprawny adres email');
            } else {
                removeError(emailInput);
            }
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('keyup', validatePassword);
        passwordInput.addEventListener('blur', () => {
            clearTimeout(passwordTimer);
            const isValid = isStrongPassword(passwordInput.value);
            markValidation(passwordInput, isValid);
            if (!isValid && passwordInput.value.length > 0) {
                showError(passwordInput, 'Hasło musi zawierać minimum 6 znaków');
            } else {
                removeError(passwordInput);
            }
        });
    }

    if (confirmedPasswordInput) {
        confirmedPasswordInput.addEventListener('keyup', validateConfirmPassword);
        confirmedPasswordInput.addEventListener('blur', () => {
            clearTimeout(confirmPasswordTimer);
            const isValid = arePasswordsSame(passwordInput.value, confirmedPasswordInput.value);
            markValidation(confirmedPasswordInput, isValid);
            if (!isValid && confirmedPasswordInput.value.length > 0) {
                showError(confirmedPasswordInput, 'Hasła nie są identyczne');
            } else {
                removeError(confirmedPasswordInput);
            }
        });
    }

    if (nameInput) {
        nameInput.addEventListener('keyup', validateName);
        nameInput.addEventListener('blur', () => {
            clearTimeout(nameTimer);
            const isValid = isValidName(nameInput.value);
            markValidation(nameInput, isValid);
            if (!isValid && nameInput.value.length > 0) {
                if (nameInput.value.length < 2) {
                    showError(nameInput, 'Imię i nazwisko musi zawierać minimum 2 znaki');
                } else {
                    showError(nameInput, 'Dozwolone są tylko litery, spacje i myślniki');
                }
            } else {
                removeError(nameInput);
            }
        });
    }

    form.addEventListener('submit', (e) => {
        let isFormValid = true;
        const errors = [];

        clearTimeout(emailTimer);
        clearTimeout(passwordTimer);
        clearTimeout(confirmPasswordTimer);
        clearTimeout(nameTimer);

        if (emailInput) {
            const emailValid = isEmail(emailInput.value);
            markValidation(emailInput, emailValid);
            if (!emailValid) {
                isFormValid = false;
                showError(emailInput, 'Wprowadź poprawny adres email');
            } else {
                removeError(emailInput);
            }
        }

        if (passwordInput && confirmedPasswordInput) {
            const passwordValid = isStrongPassword(passwordInput.value);
            markValidation(passwordInput, passwordValid);
            if (!passwordValid) {
                isFormValid = false;
                showError(passwordInput, 'Hasło musi zawierać minimum 6 znaków');
            } else {
                removeError(passwordInput);
            }
        }

        if (confirmedPasswordInput && passwordInput) {
            const confirmValid = arePasswordsSame(passwordInput.value, confirmedPasswordInput.value);
            markValidation(confirmedPasswordInput, confirmValid);
            if (!confirmValid) {
                isFormValid = false;
                showError(confirmedPasswordInput, 'Hasła nie są identyczne');
            } else {
                removeError(confirmedPasswordInput);
            }
        }

        if (nameInput) {
            const nameValid = isValidName(nameInput.value);
            markValidation(nameInput, nameValid);
            if (!nameValid) {
                isFormValid = false;
                if (nameInput.value.length < 2) {
                    showError(nameInput, 'Imię i nazwisko musi zawierać minimum 2 znaki');
                } else {
                    showError(nameInput, 'Dozwolone są tylko litery, spacje i myślniki');
                }
            } else {
                removeError(nameInput);
            }
        }

        if (!isFormValid) {
            e.preventDefault();
        }
    });
});

