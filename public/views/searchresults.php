<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/public/styles/main.css" />
    <link rel="stylesheet" href="/public/styles/mainpage.css" />
    <link rel="stylesheet" href="/public/styles/categorypage.css" />
    <link rel="stylesheet" href="/public/fontawesome/css/all.min.css" />
    <title>Wyniki wyszukiwania - FixUp</title>
  </head>
  <body>
    <header class="nav">
      <a href="/">
        <img src="/public/images/logo_fixup.svg" alt="FixUp" class="logo" />
      </a>

      <nav class="nav-links">
        <a href="/#categories">Usługi</a>
        <a href="/#steps">Jak to działa</a>
      </nav>

      <div class="nav-actions">
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
          <a href="/login" class="btn btn-secondary">Zaloguj się</a>
          <a href="/register" class="btn btn-primary">Załóż konto</a>
        <?php endif; ?>
      </div>
    </header>

    <main class="category-page container">
      <div class="breadcrumb">
        <a href="/">Strona główna</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>Wyniki wyszukiwania</span>
      </div>

      <div class="category-header">
        <h1>Wyniki wyszukiwania</h1>
        <p>
          <?php if ($searchQuery): ?>
            Szukasz: <strong><?= htmlspecialchars($searchQuery) ?></strong>
          <?php endif; ?>
          <?php if ($searchCity): ?>
            <?= $searchQuery ? ' w mieście' : 'Miasto:' ?> <strong><?= htmlspecialchars($searchCity) ?></strong>
          <?php endif; ?>
        </p>
      </div>

      <form class="search-inline" action="/search" method="GET">
        <div class="filter-group">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="q" placeholder="Czego szukasz?" value="<?= htmlspecialchars($searchQuery) ?>" />
        </div>
        <div class="filter-group">
          <i class="fa-solid fa-location-dot"></i>
          <input type="text" name="city" placeholder="Miasto" value="<?= htmlspecialchars($searchCity) ?>" />
        </div>
        <button type="submit" class="btn btn-primary">Szukaj</button>
      </form>

      <div class="workers-count">
        Znaleziono <strong><?= count($workers) ?></strong> fachowców
      </div>

      <div class="workers-grid" style="grid-column: 1 / -1;">
        <?php if (empty($workers)): ?>
          <div class="no-workers-message">
            <i class="fa-solid fa-search"></i>
            <h3>Brak wyników</h3>
            <p>Nie znaleziono fachowców pasujących do Twojego wyszukiwania.</p>
            <a href="/" class="btn btn-primary">Wróć do strony głównej</a>
          </div>
        <?php else: ?>
          <?php foreach ($workers as $worker): ?>
            <a href="/worker/<?= $worker['id'] ?>" class="worker-card">
              <?php
                $workerImage = $worker['profile_image']
                  ? '/uploads/profiles/' . $worker['profile_image']
                  : '/public/images/default-avatar.svg';
              ?>
              <img src="<?= htmlspecialchars($workerImage) ?>" alt="<?= htmlspecialchars($worker['name']) ?>" class="worker-image" />

              <div class="worker-info">
                <h3><?= htmlspecialchars($worker['name']) ?></h3>
                <span class="worker-category-badge"><?= htmlspecialchars($worker['category_name']) ?></span>

                <div class="worker-meta">
                  <?php if ($worker['city']): ?>
                    <span class="worker-location">
                      <i class="fa-solid fa-location-dot"></i>
                      <?= htmlspecialchars($worker['city']) ?>
                    </span>
                  <?php endif; ?>
                </div>

                <div class="worker-rating">
                  <?php
                    $rating = floatval($worker['rating']);
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
                  <span><?= number_format($rating, 1) ?></span>
                </div>
              </div>

              <div class="worker-action">
                <span class="btn btn-primary">Zobacz profil</span>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </main>

    <footer class="footer">
      <div class="footer-bottom">© 2025 FixUp. Wszelkie prawa zastrzeżone.</div>
    </footer>

    <script src="/public/scripts/mainpage.js"></script>
  </body>
</html>

