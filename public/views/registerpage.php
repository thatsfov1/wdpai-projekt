<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="/public/styles/main.css" />
    <link rel="stylesheet" type="text/css" href="/public/styles/loginpage.css" />
    <link rel="stylesheet" href="/public/fontawesome/css/all.min.css" />
    <title>Rejestracja - FixUp</title>
  </head>
  <body>
    <div class="login-container">
      <div class="logo-container">
        <a href="/">
          <div class="logo">
            <img src="/public/images/logo_fixup.svg" alt="FixUp Logo" />
          </div>
        </a>
        <div class="line"></div>
      </div>

      <div class="form-container">
        <h2>Zarejestruj się</h2>

        <?php if (isset($messages)): ?>
          <?php foreach ($messages as $message): ?>
            <div class="message <?= $message['type'] ?? 'error' ?>">
              <?= htmlspecialchars($message['text']) ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <form action="/register" method="POST" id="registerForm">
          <label for="name">Imię i nazwisko</label>
          <input
            type="text"
            id="name"
            name="name"
            placeholder="Wprowadź imię i nazwisko"
            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
            required
          />

          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="Wprowadź swój email"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            required
          />

          <label for="password">Hasło</label>
          <div class="password-wrapper">
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Minimum 6 znaków"
              required
            />
            <div class="eye-icon">
              <i class="fa-regular fa-eye"></i>
              <i class="fa-regular fa-eye-slash"></i>
            </div>
          </div>

          <label for="password2">Potwierdź hasło</label>
          <div class="password-wrapper">
            <input
              type="password"
              id="password2"
              name="password2"
              placeholder="Powtórz hasło"
              required
            />
            <div class="eye-icon">
              <i class="fa-regular fa-eye"></i>
              <i class="fa-regular fa-eye-slash"></i>
            </div>
          </div>

          <label>Typ konta</label>
          <div class="role-selector">
            <?php 
              $selectedRole = $_POST['role'] ?? $_GET['role'] ?? 'client';
            ?>
            <label class="role-option">
              <input
                type="radio"
                name="role"
                value="client"
                <?= ($selectedRole === 'client') ? 'checked' : '' ?>
              />
              <div class="role-card">
                <i class="fa-solid fa-user"></i>
                <span>Klient</span>
                <small>Szukam fachowca</small>
              </div>
            </label>
            <label class="role-option">
              <input
                type="radio"
                name="role"
                value="worker"
                <?= ($selectedRole === 'worker') ? 'checked' : '' ?>
              />
              <div class="role-card">
                <i class="fa-solid fa-tools"></i>
                <span>Fachowiec</span>
                <small>Oferuję usługi</small>
              </div>
            </label>
          </div>

          <div id="workerFields" class="worker-fields" style="display: none;">
            <div class="worker-fields-header">
              <i class="fa-solid fa-briefcase"></i>
              <span>Uzupełnij dane fachowca</span>
            </div>

            <label for="category_id">Kategoria usług</label>
            <select id="category_id" name="category_id">
              <option value="">Wybierz kategorię</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>

            <label for="city">Miasto</label>
            <input
              type="text"
              id="city"
              name="city"
              placeholder="W jakim mieście działasz?"
              value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"
            />

            <label for="phone">Telefon kontaktowy</label>
            <input
              type="tel"
              id="phone"
              name="phone"
              placeholder="+48 123 456 789"
              value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
            />

            <label for="experience">Lata doświadczenia</label>
            <select id="experience" name="experience">
              <option value="0">Brak doświadczenia</option>
              <option value="1" <?= (isset($_POST['experience']) && $_POST['experience'] == '1') ? 'selected' : '' ?>>1 rok</option>
              <option value="2" <?= (isset($_POST['experience']) && $_POST['experience'] == '2') ? 'selected' : '' ?>>2 lata</option>
              <option value="3" <?= (isset($_POST['experience']) && $_POST['experience'] == '3') ? 'selected' : '' ?>>3-5 lat</option>
              <option value="5" <?= (isset($_POST['experience']) && $_POST['experience'] == '5') ? 'selected' : '' ?>>5-10 lat</option>
              <option value="10" <?= (isset($_POST['experience']) && $_POST['experience'] == '10') ? 'selected' : '' ?>>Ponad 10 lat</option>
            </select>

            <label for="description">Krótki opis</label>
            <textarea
              id="description"
              name="description"
              rows="3"
              placeholder="Opisz swoje doświadczenie i specjalizację..."
            ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
          </div>

          <button type="submit">Zarejestruj się</button>
        </form>

        <div class="footer-options">
          <div>
            <span>Masz już konto?</span>
            <a href="/login">Zaloguj się</a>
          </div>
        </div>
      </div>
    </div>

    <script src="/public/scripts/login.js"></script>
    <script src="/public/scripts/validation.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const roleInputs = document.querySelectorAll('input[name="role"]');
        const workerFields = document.getElementById('workerFields');

        function toggleWorkerFields() {
          const selectedRole = document.querySelector('input[name="role"]:checked').value;
          workerFields.style.display = selectedRole === 'worker' ? 'block' : 'none';
        }

        roleInputs.forEach(input => {
          input.addEventListener('change', toggleWorkerFields);
        });

        toggleWorkerFields();
      });
    </script>
  </body>
</html>
