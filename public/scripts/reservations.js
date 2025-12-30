function openReviewModal(reservationId, workerId, workerName) {
    document.getElementById('reviewReservationId').value = reservationId;
    document.getElementById('reviewWorkerId').value = workerId;
    document.getElementById('reviewWorkerName').textContent = workerName;
    document.getElementById('reviewModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.remove('show');
    document.body.style.overflow = '';
}

document.getElementById('reviewModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeReviewModal();
    }
});

document.getElementById('reviewImages').addEventListener('change', function (e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    for (const file of this.files) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    }
});