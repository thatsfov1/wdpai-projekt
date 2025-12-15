<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/styles/main.css">
    <link rel="stylesheet" href="/public/styles/category.css">
    <link rel="stylesheet" href="/public/fontawesome/css/all.min.css">
    <title><?= htmlspecialchars($categoryName) ?> - FixUp</title>
</head>
<body>

    <header class="header">
        <div class="header-content">
            <a href="/" class="logo">
                <img src="/public/images/logo_fixup.svg" alt="FixUp">
            </a>
            <div class="search-container">
<div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Szukaj usług lub biznesów">
            </div>
            <div class="search-bar">
                <i class="fa-solid fa-location-dot"></i>
                <input type="text" placeholder="Gdzie?">
            </div>
            <div class="search-bar">
                <i class="fa-regular fa-clock"></i>
                <input type="text" placeholder="Kiedy?">
            </div>
            </div>
            
            
            <nav class="header-nav">
                <a href="/login" class="login-btn">Zaloguj się</a>
                <a href="/register" class="register-btn">Załóż konto</a>
            </nav>
        </div>
    </header>

    <section class="categories-nav">
        <nav class="categories-list">
            <?php foreach ($categories as $slug => $cat): ?>
                <a href="/category/<?= $slug ?>" class="<?= $categorySlug === $slug ? 'active' : '' ?>"><?= htmlspecialchars($cat['name']) ?></a>
            <?php endforeach; ?>
        </nav>
    </section>

    <section class="category-hero">
        <div class="container">
            <h1><?= htmlspecialchars($categoryName) ?></h1>
            <p><?= htmlspecialchars($categoryDescription) ?></p>
        </div>
    </section>

    <main class="main-content container">
        <section class="results">
            <div class="results-header">
                <div class="results-left">
                    <button class="btn-filter-toggle" id="openFilters">
                        <i class="fa-solid fa-sliders"></i> Filtry
                    </button>
                    <div class="results-sort">
                        <label>Sortuj według:</label>
                        <select>
                            <option>Polecane</option>
                            <option>Cena: od najniższej</option>
                            <option>Cena: od najwyższej</option>
                            <option>Najbliżej</option>
                        </select>
                    </div>
                </div>
                <span class="results-count"><?= $totalWorkers ?> fachowców</span>
            </div>

            <div class="workers-list">
                <?php foreach ($workers as $worker): ?>
                <article class="worker-card">
                    <a href="/worker/<?= $worker['id'] ?>" class="worker-image">
                        <img src="/public/images/mainpage/<?= htmlspecialchars($worker['image']) ?>" alt="<?= htmlspecialchars($worker['name']) ?>">
                    </a>
                    <div class="worker-info">
                        <a href="/worker/<?= $worker['id'] ?>" class="worker-name-link">
                            <h3><?= htmlspecialchars($worker['name']) ?></h3>
                        </a>
                        <p class="worker-address">
                            <i class="fa-solid fa-location-dot"></i>
                            <?= htmlspecialchars($worker['address']) ?>
                        </p>
                        <div class="worker-services">
                            <?php foreach ($worker['services'] as $service): ?>
                            <div class="service-row">
                                <span class="service-name"><?= htmlspecialchars($service['name']) ?></span>
                                <span class="service-price"><?= $service['price'] ?> zł</span>
                                <a href="/worker/<?= $worker['id'] ?>" class="btn btn-book">Umów</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <nav class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>" class="page-link">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="page-link <?= $i === $currentPage ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>" class="page-link">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <h4>O NAS</h4>
                <a href="#">O firmie</a>
                <a href="#">Kariera</a>
                <a href="#">Prasa</a>
            </div>
            <div class="footer-column">
                <h4>WSPARCIE</h4>
                <a href="#">FAQ</a>
                <a href="#">Kontakt</a>
                <a href="#">Centrum pomocy</a>
            </div>
            <div class="footer-column">
                <h4>PRAWNE</h4>
                <a href="#">Regulamin</a>
                <a href="#">Polityka prywatności</a>
            </div>
            <div class="footer-logo">
                <img src="/public/images/logo_fixup.svg" alt="FixUp">
            </div>
        </div>
        <div class="footer-bottom">
            © 2025 FixUp. Wszelkie prawa zastrzeżone.
        </div>
    </footer>

    <div class="modal-overlay" id="filtersModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Filtry</h2>
                <button class="modal-close" id="closeFilters">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="filter-section">
                    <h3>Lokalizacja</h3>
                    <div class="filter-input">
                        <i class="fa-solid fa-location-dot"></i>
                        <input type="text" placeholder="Wpisz miasto...">
                    </div>
                </div>
                
                <div class="filter-section">
                    <h3>Cena</h3>
                    <div class="price-range">
                        <input type="number" placeholder="Od" min="0">
                        <span>-</span>
                        <input type="number" placeholder="Do" min="0">
                        <span>zł</span>
                    </div>
                </div>
                
                <div class="filter-section">
                    <h3>Ocena</h3>
                    <label class="filter-checkbox">
                        <input type="checkbox"> 
                        <span class="stars"><i class="fa-solid fa-star"></i> 4.5+</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox">
                        <span class="stars"><i class="fa-solid fa-star"></i> 4.0+</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox">
                        <span class="stars"><i class="fa-solid fa-star"></i> 3.5+</span>
                    </label>
                </div>
                
                <div class="filter-section">
                    <h3>Dostępność</h3>
                    <label class="filter-checkbox">
                        <input type="checkbox"> Dostępny dziś
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox"> Dostępny jutro
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox"> Dostępny w weekend
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="clearFilters">Wyczyść</button>
                <button class="btn btn-primary" id="applyFilters">Zastosuj filtry</button>
            </div>
        </div>
    </div>

    <script src="public/scripts/category.js"></script>
</body>
</html>
