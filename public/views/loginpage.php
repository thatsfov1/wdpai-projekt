<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="/public/styles/main.css" />
    <link rel="stylesheet" type="text/css" href="/public/styles/loginpage.css" />
    <link rel="stylesheet" href="/public/fontawesome/css/all.min.css" />
    <title>Logowanie - FixUp</title>
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
        <h2>Zaloguj się</h2>

        <?php if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']): ?>
        <?php unset($_SESSION['registration_success']); ?>
        <div class="message success">
          Rejestracja zakończona pomyślnie! Możesz się teraz zalogować.
        </div>
        <?php endif; ?>

        <?php if (isset($messages)): ?>
        <?php foreach ($messages as $message): ?>
        <div class="message <?= $message['type'] ?? 'error' ?>">
          <?= htmlspecialchars($message['text']) ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <form action="/login" method="POST">
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
              placeholder="Wprowadź hasło"
              required
            />
            <div class="eye-icon">
              <i class="fa-regular fa-eye"></i>
              <i class="fa-regular fa-eye-slash"></i>
            </div>
          </div>

          <button type="submit">Zaloguj się</button>
        </form>

        <div class="footer-options">
          <div>
            <span>Nie masz konta?</span>
            <a href="/register">Zarejestruj się</a>
          </div>
        </div>
      </div>
    </div>

    <script src="/public/scripts/login.js"></script>
    <script src="/public/scripts/validation.js"></script>
  </body>
</html>
