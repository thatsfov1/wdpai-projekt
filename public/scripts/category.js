document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('filtersModal');
    const openBtn = document.getElementById('openFilters');
    const closeBtn = document.getElementById('closeFilters');
    const applyBtn = document.getElementById('applyFilters');
    const clearBtn = document.getElementById('clearFilters');
    const sortSelect = document.getElementById('sortSelect');

    const searchModal = document.getElementById('searchModal');
    const openSearchBtn = document.getElementById('openSearchModal');
    const closeSearchBtn = document.getElementById('closeSearchModal');

    if (openSearchBtn && searchModal) {
        openSearchBtn.addEventListener('click', () => searchModal.classList.add('active'));
    }
    if (closeSearchBtn && searchModal) {
        closeSearchBtn.addEventListener('click', () => searchModal.classList.remove('active'));
    }
    if (searchModal) {
        searchModal.addEventListener('click', (e) => {
            if (e.target === searchModal) searchModal.classList.remove('active');
        });
    }

    if (openBtn && modal) {
        openBtn.addEventListener('click', () => modal.classList.add('active'));
    }
    if (closeBtn && modal) {
        closeBtn.addEventListener('click', () => modal.classList.remove('active'));
    }
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('active');
        });
    }

    const buildUrl = (params) => {
        const url = new URL(window.location.href);
        Object.entries(params).forEach(([key, value]) => {
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
        });
        url.searchParams.delete('page');
        return url.toString();
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            window.location.href = buildUrl({ sort: this.value });
        });
    }

    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            const cityInput = modal.querySelector('input[placeholder*="miasto"]');
            const priceFromInput = modal.querySelector('input[placeholder="Od"]');
            const priceToInput = modal.querySelector('input[placeholder="Do"]');
            const ratingCheckboxes = modal.querySelectorAll('input[name="rating"]:checked');
            const experienceCheckboxes = modal.querySelectorAll('input[name="experience"]:checked');

            const params = {};

            if (cityInput && cityInput.value) {
                params.city = cityInput.value;
            }
            if (priceFromInput && priceFromInput.value) {
                params.price_from = priceFromInput.value;
            }
            if (priceToInput && priceToInput.value) {
                params.price_to = priceToInput.value;
            }
            if (ratingCheckboxes.length > 0) {
                const ratings = Array.from(ratingCheckboxes).map(cb => parseFloat(cb.value));
                params.min_rating = Math.min(...ratings);
            }
            if (experienceCheckboxes.length > 0) {
                const experiences = Array.from(experienceCheckboxes).map(cb => parseInt(cb.value));
                params.min_experience = Math.min(...experiences);
            }

            window.location.href = buildUrl(params);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            modal.querySelectorAll('input[type="text"], input[type="number"]').forEach(input => input.value = '');
            modal.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);

            const url = new URL(window.location.href);
            const pathname = url.pathname;
            window.location.href = pathname;
        });
    }

    const urlParams = new URLSearchParams(window.location.search);

    if (modal) {
        const cityInput = modal.querySelector('input[placeholder*="miasto"]');
        if (cityInput && urlParams.get('city')) {
            cityInput.value = urlParams.get('city');
        }

        const priceFromInput = modal.querySelector('input[placeholder="Od"]');
        if (priceFromInput && urlParams.get('price_from')) {
            priceFromInput.value = urlParams.get('price_from');
        }

        const priceToInput = modal.querySelector('input[placeholder="Do"]');
        if (priceToInput && urlParams.get('price_to')) {
            priceToInput.value = urlParams.get('price_to');
        }

        const minRating = urlParams.get('min_rating');
        if (minRating) {
            modal.querySelectorAll('input[name="rating"]').forEach(cb => {
                if (parseFloat(cb.value) >= parseFloat(minRating)) {
                    cb.checked = true;
                }
            });
        }

        const minExperience = urlParams.get('min_experience');
        if (minExperience) {
            modal.querySelectorAll('input[name="experience"]').forEach(cb => {
                if (parseInt(cb.value) >= parseInt(minExperience)) {
                    cb.checked = true;
                }
            });
        }
    }
});