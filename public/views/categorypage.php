<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/styles/main.css">
    <link rel="stylesheet" href="/public/styles/categorypage.css">
    <link rel="stylesheet" href="/public/fontawesome/css/all.min.css">
    <title><?= htmlspecialchars($categoryName) ?> - FixUp</title>
</head>
<body>

    <header class="header">
        <div class="header-content">
            <div class="header-left">
<a href="/" class="logo">
                <img src="/public/images/logo_fixup.svg" alt="FixUp">
            </a>
            <form class="search-container desktop-search" action="/search" method="GET" id="headerSearchForm">
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="q" placeholder="Szukaj usług lub biznesów" id="headerSearchQuery">
                </div>
                <div class="search-bar">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" name="city" placeholder="Gdzie?" id="headerSearchCity" value="<?= htmlspecialchars($_GET['city'] ?? '') ?>">
                </div>
                <button type="submit" class="search-submit-btn">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
            <button class="mobile-search-btn" id="openSearchModal">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Szukaj</span>
            </button>
            </div>
            
            
            
            <nav class="header-nav">
                <?php if ($user): ?>
                  <div class="profile-dropdown">
                    <button class="profile-trigger">
                      <?php
                        require_once __DIR__ . '/../../src/repository/UserRepository.php';
                        $userRepo = new UserRepository();
                        $fullUser = $userRepo->findById($user['id']);
                        $profileImage = $fullUser ? $fullUser->getProfileImageUrl() : '/public/images/default-avatar.svg';
                      ?>
                      <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profil" class="profile-avatar" />
                      <span><?= htmlspecialchars($user['name']) ?></span>
                      <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a href="/profile"><i class="fa-solid fa-user"></i> Mój profil</a>
                      <a href="/reservations"><i class="fa-solid fa-calendar-check"></i> Moje rezerwacje</a>
                      <?php if ($user['role'] === 'worker'): ?>
                        <a href="/profile#services"><i class="fa-solid fa-briefcase"></i> Moje usługi</a>
                      <?php endif; ?>
                      <div class="dropdown-divider"></div>
                      <a href="/logout" class="logout-link"><i class="fa-solid fa-sign-out-alt"></i> Wyloguj się</a>
                    </div>
                  </div>
                <?php else: ?>
                  <a href="/login" class="login-btn">Zaloguj się</a>
                  <a href="/register" class="register-btn">Załóż konto</a>
                <?php endif; ?>
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
                        <select id="sortSelect">
                            <option value="rating" <?= ($sort ?? '') === 'rating' ? 'selected' : '' ?>>Najwyżej oceniane</option>
                            <option value="price_low" <?= ($sort ?? '') === 'price_low' ? 'selected' : '' ?>>Cena: od najniższej</option>
                            <option value="price_high" <?= ($sort ?? '') === 'price_high' ? 'selected' : '' ?>>Cena: od najwyższej</option>
                            <option value="experience" <?= ($sort ?? '') === 'experience' ? 'selected' : '' ?>>Doświadczenie</option>
                        </select>
                    </div>
                </div>
                <span class="results-count"><?= $totalWorkers ?> fachowców</span>
            </div>

            <div class="workers-list">
                <?php if (empty($workers)): ?>
                <div class="no-results">
                    <i class="fa-solid fa-search"></i>
                    <h3>Brak wyników</h3>
                    <p>Nie znaleziono fachowców w tej kategorii spełniających wybrane kryteria.</p>
                    <a href="/category/<?= htmlspecialchars($categorySlug) ?>" class="btn btn-primary">Wyczyść filtry</a>
                </div>
                <?php else: ?>
                <?php foreach ($workers as $worker): ?>
                <article class="worker-card">
                    <div class="worker-card-header">
                        <a href="/worker/<?= $worker['id'] ?>" class="worker-image">
                            <img src="<?= htmlspecialchars($worker['image']) ?>" alt="<?= htmlspecialchars($worker['name']) ?>">
                        </a>
                        <div class="worker-header-info">
                            <a href="/worker/<?= $worker['id'] ?>" class="worker-name-link">
                                <h3><?= htmlspecialchars($worker['name']) ?></h3>
                            </a>
                            <p class="worker-address">
                                <i class="fa-solid fa-location-dot"></i>
                                <?= htmlspecialchars($worker['address']) ?>
                            </p>
                            <div class="worker-rating">
                                <?php 
                                    $rating = floatval($worker['rating'] ?? 0);
                                    $reviewsCount = intval($worker['reviews_count'] ?? 0);
                                    for ($i = 1; $i <= 5; $i++): 
                                        if ($i <= $rating): ?>
                                            <i class="fa-solid fa-star"></i>
                                        <?php elseif ($i - 0.5 <= $rating): ?>
                                            <i class="fa-solid fa-star-half-stroke"></i>
                                        <?php else: ?>
                                            <i class="fa-regular fa-star"></i>
                                        <?php endif;
                                    endfor; 
                                ?>
                                <span class="rating-value"><?= number_format($rating, 1) ?></span>
                                <span class="reviews-count">(<?= $reviewsCount ?>)</span>
                            </div>
                        </div>
                    </div>
                    <div class="worker-services">
                        <?php foreach ($worker['services'] as $service): ?>
                        <div class="service-row">
                            <span class="service-name"><?= htmlspecialchars($service['name']) ?></span>
                            <span class="service-price"><?= $service['price'] ?> zł</span>
                            <a href="/worker/<?= $worker['id'] ?>" class="btn btn-book">Umów</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>
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
          <img src="/public/images/logo_fixup.svg" alt="FixUp" />
        </div>
      </div>

      <div class="footer-bottom">© 2025 FixUp. Wszelkie prawa zastrzeżone.</div>
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
                        <input type="checkbox" name="rating" value="4.5"> 
                        <span class="stars"><i class="fa-solid fa-star"></i> 4.5+</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="rating" value="4.0">
                        <span class="stars"><i class="fa-solid fa-star"></i> 4.0+</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="rating" value="3.5">
                        <span class="stars"><i class="fa-solid fa-star"></i> 3.5+</span>
                    </label>
                </div>
                
                <div class="filter-section">
                    <h3>Doświadczenie</h3>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="experience" value="10"> 10+ lat
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="experience" value="5"> 5+ lat
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="experience" value="2"> 2+ lat
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="clearFilters">Wyczyść</button>
                <button class="btn btn-primary" id="applyFilters">Zastosuj filtry</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="searchModal">
        <div class="modal search-modal">
            <div class="modal-header">
                <h2><i class="fa-solid fa-magnifying-glass"></i> Wyszukaj</h2>
                <button class="modal-close" id="closeSearchModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form class="modal-body" action="/search" method="GET">
                <div class="search-modal-field">
                    <label>Czego szukasz?</label>
                    <div class="search-modal-input">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="q" placeholder="np. Hydraulik, Elektryk, Malowanie...">
                    </div>
                </div>
                <div class="search-modal-field">
                    <label>Lokalizacja</label>
                    <div class="search-modal-input">
                        <i class="fa-solid fa-location-dot"></i>
                        <input type="text" name="city" placeholder="Wpisz miasto..." value="<?= htmlspecialchars($_GET['city'] ?? '') ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary search-modal-submit">
                    <i class="fa-solid fa-magnifying-glass"></i> Szukaj
                </button>
            </form>
        </div>
    </div>

    <script src="/public/scripts/category.js"></script>
    <script src="/public/scripts/mainpage.js"></script>
</body>
</html>