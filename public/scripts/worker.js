const tabBtns = document.querySelectorAll('.tab-btn');
for (let i = 0; i < tabBtns.length; i++) {
    tabBtns[i].addEventListener('click', () => {
        const tabId = tabBtns[i].dataset.tab;

        const allTabBtns = document.querySelectorAll('.tab-btn');
        for (let j = 0; j < allTabBtns.length; j++) {
            allTabBtns[j].classList.remove('active');
        }
        tabBtns[i].classList.add('active');

        const tabContents = document.querySelectorAll('.tab-content');
        for (let j = 0; j < tabContents.length; j++) {
            tabContents[j].classList.remove('active');
        }
        document.getElementById('tab-' + tabId).classList.add('active');
    });
}

const seeMoreLinks = document.querySelectorAll('.see-more[data-tab-link]');
for (let i = 0; i < seeMoreLinks.length; i++) {
    seeMoreLinks[i].addEventListener('click', (e) => {
        e.preventDefault();
        const tabId = seeMoreLinks[i].dataset.tabLink;
        document.querySelector(`.tab-btn[data-tab="${tabId}"]`).click();
    });
}

const dayBtns = document.querySelectorAll('.day-btn');
for (let i = 0; i < dayBtns.length; i++) {
    dayBtns[i].addEventListener('click', () => {
        const allDayBtns = document.querySelectorAll('.day-btn');
        for (let j = 0; j < allDayBtns.length; j++) {
            allDayBtns[j].classList.remove('selected');
        }
        dayBtns[i].classList.add('selected');
    });
}

const timeSlots = document.querySelectorAll('.time-slot');
for (let i = 0; i < timeSlots.length; i++) {
    timeSlots[i].addEventListener('click', () => {
        const allTimeSlots = document.querySelectorAll('.time-slot');
        for (let j = 0; j < allTimeSlots.length; j++) {
            allTimeSlots[j].classList.remove('selected');
        }
        timeSlots[i].classList.add('selected');
    });
}

const bookingForm = document.getElementById('bookingForm');
const dateInput = document.getElementById('booking_date');

if (bookingForm && dateInput) {
    const updateTimeSlots = () => {
        const selectedDate = dateInput.value;
        const today = new Date().toISOString().split('T')[0];
        const now = new Date();
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();

        const timeSlotLabels = document.querySelectorAll('.time-slot-label');
        for (let i = 0; i < timeSlotLabels.length; i++) {
            const label = timeSlotLabels[i];
            const radio = label.querySelector('input[type="radio"]');
            const slot = label.querySelector('.time-slot');
            const timeValue = radio.value;
            const [hours, minutes] = timeValue.split(':').map(Number);

            if (selectedDate === today) {
                if (hours < currentHour || (hours === currentHour && minutes <= currentMinute)) {
                    radio.disabled = true;
                    slot.classList.add('disabled');
                    if (radio.checked) {
                        radio.checked = false;
                    }
                } else {
                    radio.disabled = false;
                    slot.classList.remove('disabled');
                }
            } else {
                radio.disabled = false;
                slot.classList.remove('disabled');
            }
        }

        const checkedRadio = document.querySelector('.time-slot-label input[type="radio"]:checked');
        if (!checkedRadio) {
            const firstAvailable = document.querySelector('.time-slot-label input[type="radio"]:not(:disabled)');
            if (firstAvailable) {
                firstAvailable.checked = true;
            }
        }
    }

    dateInput.addEventListener('change', updateTimeSlots);

    updateTimeSlots();

    bookingForm.addEventListener('submit', (e) => {
        const selectedDate = dateInput.value;
        const selectedTime = document.querySelector('.time-slot-label input[type="radio"]:checked');

        if (!selectedTime) {
            e.preventDefault();
            alert('Proszę wybrać godzinę rezerwacji');
            return;
        }

        const today = new Date().toISOString().split('T')[0];
        if (selectedDate === today) {
            const now = new Date();
            const [hours, minutes] = selectedTime.value.split(':').map(Number);
            const reservationTime = new Date();
            reservationTime.setHours(hours, minutes, 0, 0);

            if (reservationTime <= now) {
                e.preventDefault();
                alert('Nie można zarezerwować terminu w przeszłości. Wybierz późniejszą godzinę');
                return;
            }
        }
    });
}