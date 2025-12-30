document.addEventListener('DOMContentLoaded', function () {
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

    function isEmail(email) {
        return /\S+@\S+\.\S+/.test(email);
    }

    function isStrongPassword(password) {
        return password.length >= 6;
    }

    function arePasswordsSame(password, confirmedPassword) {
        return password === confirmedPassword && confirmedPassword.length > 0;
    }

    function isValidName(name) {
        if (name.length < 2) {
            return false;
        }
        const nameRegex = /^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ\s\-]+$/;
        return nameRegex.test(name);
    }

    function markValidation(element, condition) {
        if (!element) return;

        if (!condition) {
            element.classList.add('no-valid');
            element.classList.remove('valid');
        } else {
            element.classList.remove('no-valid');
            element.classList.add('valid');
        }
    }

    function showError(element, message) {
        if (!element) return;

        removeError(element);

        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error';
        errorDiv.textContent = message;

        const wrapper = element.closest('.password-wrapper') || element;
        wrapper.parentNode.insertBefore(errorDiv, wrapper.nextSibling);
    }

    function removeError(element) {
        if (!element) return;

        const wrapper = element.closest('.password-wrapper') || element;
        const existingError = wrapper.parentNode.querySelector('.validation-error');
        if (existingError) {
            existingError.remove();
        }
    }

    function validateEmail() {
        if (!emailInput) return;

        clearTimeout(emailTimer);
        emailTimer = setTimeout(function () {
            const isValid = isEmail(emailInput.value);
            markValidation(emailInput, isValid);

            if (!isValid && emailInput.value.length > 0) {
                showError(emailInput, 'Wprowadź poprawny adres email');
            } else {
                removeError(emailInput);
            }
        }, DEBOUNCE_DELAY);
    }

    function validatePassword() {
        if (!passwordInput) return;

        clearTimeout(passwordTimer);
        passwordTimer = setTimeout(function () {
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

    function validateConfirmPassword() {
        if (!confirmedPasswordInput || !passwordInput) return;

        clearTimeout(confirmPasswordTimer);
        confirmPasswordTimer = setTimeout(function () {
            const isValid = arePasswordsSame(passwordInput.value, confirmedPasswordInput.value);
            markValidation(confirmedPasswordInput, isValid);

            if (!isValid && confirmedPasswordInput.value.length > 0) {
                showError(confirmedPasswordInput, 'Hasła nie są identyczne');
            } else {
                removeError(confirmedPasswordInput);
            }
        }, DEBOUNCE_DELAY);
    }

    function validateName() {
        if (!nameInput) return;

        clearTimeout(nameTimer);
        nameTimer = setTimeout(function () {
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
        emailInput.addEventListener('blur', function () {
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
        passwordInput.addEventListener('blur', function () {
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
        confirmedPasswordInput.addEventListener('blur', function () {
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
        nameInput.addEventListener('blur', function () {
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

    form.addEventListener('submit', function (e) {
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

