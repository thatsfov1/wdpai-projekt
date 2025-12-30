document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabId = btn.dataset.tab;

        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.getElementById('tab-' + tabId).classList.add('active');
    });
});

document.querySelectorAll('.see-more[data-tab-link]').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const tabId = link.dataset.tabLink;
        document.querySelector(`.tab-btn[data-tab="${tabId}"]`).click();
    });
});

document.querySelectorAll('.day-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.day-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
    });
});

document.querySelectorAll('.time-slot').forEach(slot => {
    slot.addEventListener('click', () => {
        document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
        slot.classList.add('selected');
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const bookingForm = document.getElementById('bookingForm');
    const dateInput = document.getElementById('booking_date');

    if (bookingForm && dateInput) {
        function updateTimeSlots() {
            const selectedDate = dateInput.value;
            const today = new Date().toISOString().split('T')[0];
            const now = new Date();
            const currentHour = now.getHours();
            const currentMinute = now.getMinutes();

            document.querySelectorAll('.time-slot-label').forEach(label => {
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
            });

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

        bookingForm.addEventListener('submit', function (e) {
            const selectedDate = dateInput.value;
            const selectedTime = document.querySelector('.time-slot-label input[type="radio"]:checked');

            if (!selectedTime) {
                e.preventDefault();
                alert('Proszę wybrać godzinę rezerwacji.');
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
                    alert('Nie można zarezerwować terminu w przeszłości. Wybierz późniejszą godzinę.');
                    return;
                }
            }
        });
    }
});