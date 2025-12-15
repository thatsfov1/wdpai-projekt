const modal = document.getElementById('filtersModal');
        const openBtn = document.getElementById('openFilters');
        const closeBtn = document.getElementById('closeFilters');
        const applyBtn = document.getElementById('applyFilters');

        openBtn.addEventListener('click', () => modal.classList.add('active'));
        closeBtn.addEventListener('click', () => modal.classList.remove('active'));
        applyBtn.addEventListener('click', () => modal.classList.remove('active'));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('active');
        });