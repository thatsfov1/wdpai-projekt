<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/public/styles/main.css" />
    <link rel="stylesheet" href="/public/styles/mainpage.css" />
    <link rel="stylesheet" href="/public/fontawesome/css/all.min.css" />
    <title>FixUp - Znajdź fachowca</title>
  </head>

  <body>
    <header class="nav">
      <a href="/">
        <img src="/public/images/logo_fixup.svg" alt="FixUp" class="logo" />
      </a>

      <button class="burger-menu" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <div class="burger-dropdown">
        <a href="#categories"><i class="fa-solid fa-th-large"></i> Usługi</a>
        <a href="#steps"><i class="fa-solid fa-circle-question"></i> Jak to działa</a>
        <a href="/register?role=worker"><i class="fa-solid fa-briefcase"></i> Dla Fachowców</a>
        <div class="dropdown-divider"></div>
        <?php if ($user): ?>
          <a href="/profile"><i class="fa-solid fa-user"></i> Mój profil</a>
          <a href="/reservations"><i class="fa-solid fa-calendar-check"></i> Moje rezerwacje</a>
          <a href="/logout" class="logout-link"><i class="fa-solid fa-sign-out-alt"></i> Wyloguj się</a>
        <?php else: ?>
          <a href="/login" class="btn btn-secondary burger-btn">Zaloguj się</a>
          <a href="/register" class="btn btn-primary burger-btn">Załóż konto</a>
        <?php endif; ?>
      </div>

      <nav class="nav-links">
        <a href="#categories">Usługi</a>
        <a href="#steps">Jak to działa</a>
        <a href="/register?role=worker">Dla Fachowców</a>
      </nav>

      <div class="nav-actions">
        <?php if ($user): ?>
          <div class="profile-dropdown">
            <button class="profile-trigger">
              <?php 
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

    <section class="hero">
      <h1>Znajdź zaufanego fachowca w swojej okolicy</h1>
      <p>Szybko, łatwo i w Twojej okolicy</p>

      <form class="search-box" action="/search" method="GET">
        <div class="input">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" name="q" placeholder="Czego potrzebujesz?" />
        </div>

        <div class="input">
          <i class="fa-solid fa-location-dot"></i>
          <input type="text" name="city" placeholder="W jakim mieście?" />
        </div>

        <button type="submit" class="btn btn-primary">Szukaj</button>
      </form>
    </section>

    <section class="categories container" id="categories">
      <h2>Popularne kategorie</h2>

      <div class="category-list">
        <?php foreach ($categories as $cat): ?>
          <a href="/category/<?= htmlspecialchars($cat['slug']) ?>" class="category">
            <div class="category-img">
              <img src="/public/images/mainpage/<?= htmlspecialchars($cat['icon']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" />
            </div>
            <span><?= htmlspecialchars($cat['name']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="steps" id="steps">
      <h2>Jak to działa</h2>

      <div class="step-list container">
        <div class="step">
          <div class="step-img">
            <i class="fa-solid fa-magnifying-glass"></i>
          </div>
          <h3>Szukaj</h3>
          <p>Opisz, czego potrzebujesz, a my znajdziemy dla Ciebie odpowiednich specjalistów.</p>
        </div>

        <div class="step">
          <div class="step-img">
            <i class="fa-solid fa-mobile-screen"></i>
          </div>
          <h3>Rezerwuj</h3>
          <p>Przeglądaj profile, czytaj opinie i wybierz najlepszego fachowca.</p>
        </div>

        <div class="step">
          <div class="step-img">
            <i class="fa-solid fa-check-double"></i>
          </div>
          <h3>Gotowe</h3>
          <p>Ciesz się dobrze wykonaną pracą i oceń specjalistę.</p>
        </div>
      </div>
    </section>

    <section class="experts">
      <div class="container">
        <h2>Polecani fachowcy</h2>

        <div class="expert-list">
          <?php if (empty($featuredWorkers)): ?>
            <p class="no-workers">Brak fachowców do wyświetlenia. <a href="/register?role=worker">Zarejestruj się jako fachowiec!</a></p>
          <?php else: ?>
            <?php foreach ($featuredWorkers as $worker): ?>
              <a href="/worker/<?= $worker['id'] ?>" class="expert">
                <?php 
                  $workerImage = $worker['profile_image'] 
                    ? '/uploads/profiles/' . $worker['profile_image'] 
                    : '/public/images/default-avatar.svg';
                ?>
                <img src="<?= htmlspecialchars($workerImage) ?>" alt="<?= htmlspecialchars($worker['name']) ?>" />
                <h4><?= htmlspecialchars($worker['name']) ?></h4>
                <span class="expert-sub"><?= htmlspecialchars($worker['category_name']) ?></span>
                <div class="rating">
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
                <?php if ($worker['city']): ?>
                  <span class="expert-location"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($worker['city']) ?></span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="cta">
      <div class="container cta-container">
        <div class="cta-box light">
          <h3>Gotowy na remont?</h3>
          <p>Znajdź swojego specjalistę i zrealizuj swoje plany.</p>
          <a href="#categories" class="btn btn-primary">Znajdź fachowca</a>
        </div>

        <div class="cta-box dark">
          <h3>Jesteś fachowcem?</h3>
          <p>Zdobądź nowych klientów i rozwijaj swój biznes z nami.</p>
          <a href="/register?role=worker" class="btn btn-white">Dołącz jako fachowiec</a>
        </div>
      </div>
    </section>

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

    <script src="/public/scripts/mainpage.js"></script>
  </body>
</html>

