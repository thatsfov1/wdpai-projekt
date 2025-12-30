<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/public/styles/main.css" />
    <link rel="stylesheet" href="/public/styles/mainpage.css" />
    <link rel="stylesheet" href="/public/styles/profile.css" />
    <link rel="stylesheet" href="/public/fontawesome/css/all.min.css" />
    <title>Mój profil - FixUp</title>
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
        <div class="profile-dropdown">
          <button class="profile-trigger">
            <img src="<?= htmlspecialchars($user->getProfileImageUrl()) ?>" alt="Profil" class="profile-avatar" />
            <span><?= htmlspecialchars($user->getName()) ?></span>
            <i class="fa-solid fa-chevron-down"></i>
          </button>
          <div class="dropdown-menu">
            <a href="/profile" class="active"><i class="fa-solid fa-user"></i> Mój profil</a>
            <a href="/reservations"><i class="fa-solid fa-calendar-check"></i> Moje rezerwacje</a>
            <?php if ($user->isWorker()): ?>
              <a href="/profile#services"><i class="fa-solid fa-briefcase"></i> Moje usługi</a>
            <?php endif; ?>
            <div class="dropdown-divider"></div>
            <a href="/logout" class="logout-link"><i class="fa-solid fa-sign-out-alt"></i> Wyloguj się</a>
          </div>
        </div>
      </div>
    </header>

    <main class="profile-page container">
      <h1>Mój profil</h1>

      <?php if (isset($_SESSION['profile_success'])): ?>
        <div class="message success"><?= htmlspecialchars($_SESSION['profile_success']) ?></div>
        <?php unset($_SESSION['profile_success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['profile_error'])): ?>
        <div class="message error"><?= htmlspecialchars($_SESSION['profile_error']) ?></div>
        <?php unset($_SESSION['profile_error']); ?>
      <?php endif; ?>

      <div class="profile-grid">
        <section class="profile-section">
          <h2>Dane osobowe</h2>
          
          <form action="/profile/update" method="POST" enctype="multipart/form-data" class="profile-form">
            <div class="profile-image-section">
              <img src="<?= htmlspecialchars($user->getProfileImageUrl()) ?>" alt="Zdjęcie profilowe" class="profile-image-large" />
              <label class="upload-btn">
                <i class="fa-solid fa-camera"></i> Zmień zdjęcie
                <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp" />
              </label>
            </div>

            <div class="form-group">
              <label for="name">Imię i nazwisko</label>
              <input type="text" id="name" name="name" value="<?= htmlspecialchars($user->getName()) ?>" required />
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" value="<?= htmlspecialchars($user->getEmail()) ?>" disabled />
              <small>Email nie może być zmieniony</small>
            </div>

            <div class="form-group">
              <label for="phone">Telefon</label>
              <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user->getPhone() ?? '') ?>" placeholder="+48 123 456 789" />
            </div>

            <div class="form-group">
              <label for="city">Miasto</label>
              <input type="text" id="city" name="city" value="<?= htmlspecialchars($user->getCity() ?? '') ?>" placeholder="Np. Kraków" />
            </div>

            <?php if ($user->isWorker() && $worker): ?>
              <h3>Dane fachowca</h3>

              <div class="form-group">
                <label for="category_id">Kategoria</label>
                <select id="category_id" name="category_id">
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $worker['category_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cat['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="experience">Doświadczenie (lata)</label>
                <input type="number" id="experience" name="experience" value="<?= $worker['experience_years'] ?>" min="0" max="50" />
              </div>

              <div class="form-group">
                <label for="hourly_rate">Stawka godzinowa (PLN)</label>
                <input type="number" id="hourly_rate" name="hourly_rate" value="<?= $worker['hourly_rate'] ?>" min="0" step="0.01" placeholder="Opcjonalne" />
              </div>

              <div class="form-group">
                <label for="description">Opis</label>
                <textarea id="description" name="description" rows="4" placeholder="Opisz swoje usługi i doświadczenie..."><?= htmlspecialchars($worker['description'] ?? '') ?></textarea>
              </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
          </form>

          <div class="account-info">
            <p><i class="fa-solid fa-calendar"></i> Konto utworzone: <?= date('d.m.Y', strtotime($user->getCreatedAt())) ?></p>
            <p><i class="fa-solid fa-user-tag"></i> Typ konta: <?= $user->isWorker() ? 'Fachowiec' : 'Klient' ?></p>
          </div>
        </section>

        <?php if ($user->isWorker() && $worker): ?>
          <section class="profile-section" id="services">
            <h2>Moje usługi</h2>

            <form action="/profile/add-service" method="POST" class="add-service-form">
              <h3>Dodaj nową usługę</h3>
              <div class="form-row">
                <div class="form-group">
                  <input type="text" name="service_name" placeholder="Nazwa usługi" required />
                </div>
                <div class="form-group">
                  <input type="number" name="service_price" placeholder="Cena (PLN)" min="0" step="0.01" />
                </div>
              </div>
              <div class="form-group">
                <textarea name="service_description" rows="2" placeholder="Opis usługi (opcjonalnie)"></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-small">Dodaj usługę</button>
            </form>

            <div class="services-list">
              <?php if (empty($services)): ?>
                <p class="no-services">Nie masz jeszcze żadnych usług. Dodaj swoją pierwszą usługę powyżej.</p>
              <?php else: ?>
                <?php foreach ($services as $service): ?>
                  <div class="service-item">
                    <div class="service-info">
                      <h4><?= htmlspecialchars($service['name']) ?></h4>
                      <?php if ($service['description']): ?>
                        <p><?= htmlspecialchars($service['description']) ?></p>
                      <?php endif; ?>
                    </div>
                    <div class="service-actions">
                      <?php if ($service['price']): ?>
                        <span class="service-price"><?= number_format($service['price'], 2) ?> PLN</span>
                      <?php endif; ?>
                      <a href="/profile/delete-service?id=<?= $service['id'] ?>" class="btn-delete" onclick="return confirm('Czy na pewno chcesz usunąć tę usługę?')">
                        <i class="fa-solid fa-trash"></i>
                      </a>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </section>
        <?php endif; ?>
      </div>
    </main>

    <footer class="footer">
      <div class="footer-bottom">© 2025 FixUp. Wszelkie prawa zastrzeżone.</div>
    </footer>

    <script src="/public/scripts/mainpage.js"></script>
    <script src="/public/scripts/profile.js"></script>
  </body>
</html>

