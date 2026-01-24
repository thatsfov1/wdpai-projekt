<?php

require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../../Database.php';

class SecurityController {
    
    private UserRepository $userRepository;
    private array $messages = [];
    
    // Konfiguracja rate limiting
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME_MINUTES = 15;
    
    // Limity długości pól
    private const MAX_NAME_LENGTH = 100;
    private const MAX_EMAIL_LENGTH = 255;
    private const MAX_PASSWORD_LENGTH = 72; // bcrypt limit
    private const MAX_PHONE_LENGTH = 20;
    private const MAX_CITY_LENGTH = 100;
    private const MAX_DESCRIPTION_LENGTH = 2000;

    public function __construct() {
        $this->userRepository = new UserRepository();
        $this->initSession();
    }

    private function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $ip = $this->getClientIp();

            // Sprawdź czy konto nie jest zablokowane (4A - rate limiting)
            if ($this->isAccountLocked($email, $ip)) {
                $remainingTime = $this->getRemainingLockoutTime($email, $ip);
                $this->messages[] = [
                    'type' => 'error',
                    'text' => "Zbyt wiele nieudanych prób logowania. Spróbuj ponownie za {$remainingTime} minut."
                ];
            } else {
                $errors = $this->validateLogin($email, $password);

                if (empty($errors)) {
                    $user = $this->userRepository->findByEmail($email);

                    if ($user && password_verify($password, $user->getPassword())) {
                        // Zapisz udaną próbę i wyczyść poprzednie nieudane
                        $this->recordLoginAttempt($email, $ip, true);
                        $this->clearFailedAttempts($email, $ip);
                        
                        session_regenerate_id(true);
                        
                        $_SESSION['user_id'] = $user->getId();
                        $_SESSION['user_email'] = $user->getEmail();
                        $_SESSION['user_name'] = $user->getName();
                        $_SESSION['user_role'] = $user->getRole();

                        header('Location: /');
                        exit();
                    } else {
                        // Zapisz nieudaną próbę
                        $this->recordLoginAttempt($email, $ip, false);
                        
                        $this->messages[] = [
                            'type' => 'error',
                            'text' => 'Nieprawidłowy email lub hasło'
                        ];
                    }
                } else {
                    foreach ($errors as $error) {
                        $this->messages[] = [
                            'type' => 'error',
                            'text' => $error
                        ];
                    }
                }
            }
        }

        $messages = $this->messages;
        include __DIR__ . '/../../public/views/loginpage.php';
    }

    public function register() {
        require_once __DIR__ . '/../repository/CategoryRepository.php';
        $categoryRepo = new CategoryRepository();
        $categories = $categoryRepo->findAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $this->sanitizeInput($_POST['name'] ?? '');
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';
            $role = $this->sanitizeInput($_POST['role'] ?? 'client');
            $phone = $this->sanitizeInput($_POST['phone'] ?? '');
            $city = $this->sanitizeInput($_POST['city'] ?? '');

            $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $experience = isset($_POST['experience']) ? (int)$_POST['experience'] : 0;
            $description = $this->sanitizeInput($_POST['description'] ?? '');

            $errors = $this->validateRegistration($name, $email, $password, $password2, $role);

            // 2D - Dodatkowa walidacja długości pól opcjonalnych
            if (!empty($phone) && strlen($phone) > self::MAX_PHONE_LENGTH) {
                $errors[] = 'Numer telefonu może zawierać maksymalnie ' . self::MAX_PHONE_LENGTH . ' znaków';
            }
            
            if (!empty($city) && mb_strlen($city) > self::MAX_CITY_LENGTH) {
                $errors[] = 'Nazwa miasta może zawierać maksymalnie ' . self::MAX_CITY_LENGTH . ' znaków';
            }
            
            if (!empty($description) && mb_strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
                $errors[] = 'Opis może zawierać maksymalnie ' . self::MAX_DESCRIPTION_LENGTH . ' znaków';
            }

            if ($role === 'worker') {
                if (empty($categoryId)) {
                    $errors[] = 'Wybierz kategorię usług';
                }
                if (empty($city)) {
                    $errors[] = 'Miasto jest wymagane dla fachowca';
                }
            }

            if (empty($errors)) {
                if ($this->userRepository->emailExists($email)) {
                    // 1B - Nie zdradzamy czy email istnieje w bazie
                    $this->messages[] = [
                        'type' => 'success',
                        'text' => 'Jeśli podany adres email nie był wcześniej zarejestrowany, konto zostało utworzone. Sprawdź swoją skrzynkę email.'
                    ];
                    // Symuluj opóźnienie jak przy prawdziwej rejestracji
                    usleep(random_int(100000, 300000));
                    header('Location: /login');
                    exit();
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    
                    $user = new User($email, $hashedPassword, $name, $role);
                    $user->setPhone($phone);
                    $user->setCity($city);
                    
                    $userId = $this->userRepository->createAndGetId($user);
                    
                    if ($userId) {
                        if ($role === 'worker' && $categoryId) {
                            require_once __DIR__ . '/../repository/WorkerRepository.php';
                            $workerRepo = new WorkerRepository();
                            $workerRepo->create($userId, $categoryId, $description, $experience);
                        }
                        
                        $_SESSION['registration_success'] = true;
                        header('Location: /login');
                        exit();
                    } else {
                        $this->messages[] = [
                            'type' => 'error',
                            'text' => 'Wystąpił błąd podczas rejestracji. Spróbuj ponownie.'
                        ];
                    }
                }
            } else {
                foreach ($errors as $error) {
                    $this->messages[] = [
                        'type' => 'error',
                        'text' => $error
                    ];
                }
            }
        }

        $messages = $this->messages;
        include __DIR__ . '/../../public/views/registerpage.php';
    }

    public function logout() {
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        header('Location: /');
        exit();
    }

    private function validateLogin(string $email, string $password): array {
        $errors = [];

        if (empty($email)) {
            $errors[] = 'Email jest wymagany';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Wprowadź poprawny adres email';
        }

        if (empty($password)) {
            $errors[] = 'Hasło jest wymagane';
        }

        return $errors;
    }

    private function validateRegistration(
        string $name,
        string $email,
        string $password,
        string $password2,
        string $role
    ): array {
        $errors = [];

        // 2D - Walidacja długości pól (min i max)
        if (empty($name)) {
            $errors[] = 'Imię i nazwisko jest wymagane';
        } elseif (mb_strlen($name) < 2) {
            $errors[] = 'Imię i nazwisko musi zawierać minimum 2 znaki';
        } elseif (mb_strlen($name) > self::MAX_NAME_LENGTH) {
            $errors[] = 'Imię i nazwisko może zawierać maksymalnie ' . self::MAX_NAME_LENGTH . ' znaków';
        } elseif (!preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ\s\-]+$/u', $name)) {
            $errors[] = 'Imię i nazwisko może zawierać tylko litery, spacje i myślniki';
        }

        if (empty($email)) {
            $errors[] = 'Email jest wymagany';
        } elseif (strlen($email) > self::MAX_EMAIL_LENGTH) {
            $errors[] = 'Adres email jest zbyt długi (max ' . self::MAX_EMAIL_LENGTH . ' znaków)';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Wprowadź poprawny adres email';
        }

        if (empty($password)) {
            $errors[] = 'Hasło jest wymagane';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Hasło musi zawierać minimum 6 znaków';
        } elseif (strlen($password) > self::MAX_PASSWORD_LENGTH) {
            $errors[] = 'Hasło może zawierać maksymalnie ' . self::MAX_PASSWORD_LENGTH . ' znaków';
        }

        if ($password !== $password2) {
            $errors[] = 'Hasła nie są identyczne';
        }

        if (!in_array($role, ['client', 'worker'])) {
            $errors[] = 'Nieprawidłowy typ konta';
        }

        return $errors;
    }

    private function sanitizeInput(string $data): string {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    public static function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public static function getCurrentUser(): ?array {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ];
    }

    public static function requireAuth(): void {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
    }

    public static function requireRole(string $role): void {
        self::requireAuth();
        
        if ($_SESSION['user_role'] !== $role) {
            http_response_code(403);
            echo "Access denied";
            exit();
        }
    }

    // ============================================
    // 4A - Rate Limiting - Ochrona przed brute-force
    // ============================================

    /**
     * Pobiera adres IP klienta
     */
    private function getClientIp(): string {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Sprawdza czy konto jest zablokowane z powodu zbyt wielu prób logowania
     */
    private function isAccountLocked(string $email, string $ip): bool {
        $failedAttempts = $this->getFailedLoginAttempts($email, $ip);
        return $failedAttempts >= self::MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Pobiera liczbę nieudanych prób logowania w oknie czasowym
     */
    private function getFailedLoginAttempts(string $email, string $ip): int {
        $database = new Database();
        $conn = $database->connect();
        
        $stmt = $conn->prepare(
            'SELECT COUNT(*) FROM login_attempts 
             WHERE (email = :email OR ip_address = :ip) 
             AND success = FALSE 
             AND attempted_at > NOW() - INTERVAL \'' . self::LOCKOUT_TIME_MINUTES . ' minutes\''
        );
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Oblicza pozostały czas blokady w minutach
     */
    private function getRemainingLockoutTime(string $email, string $ip): int {
        $database = new Database();
        $conn = $database->connect();
        
        $stmt = $conn->prepare(
            'SELECT MAX(attempted_at) FROM login_attempts 
             WHERE (email = :email OR ip_address = :ip) 
             AND success = FALSE 
             AND attempted_at > NOW() - INTERVAL \'' . self::LOCKOUT_TIME_MINUTES . ' minutes\''
        );
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
        $stmt->execute();
        
        $lastAttempt = $stmt->fetchColumn();
        
        if ($lastAttempt) {
            $lockoutEnd = strtotime($lastAttempt) + (self::LOCKOUT_TIME_MINUTES * 60);
            $remaining = ceil(($lockoutEnd - time()) / 60);
            return max(1, (int) $remaining);
        }
        
        return self::LOCKOUT_TIME_MINUTES;
    }

    /**
     * Zapisuje próbę logowania do bazy
     */
    private function recordLoginAttempt(string $email, string $ip, bool $success): void {
        $database = new Database();
        $conn = $database->connect();
        
        $stmt = $conn->prepare(
            'INSERT INTO login_attempts (email, ip_address, success, attempted_at) 
             VALUES (:email, :ip, :success, NOW())'
        );
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindParam(':success', $success, PDO::PARAM_BOOL);
        $stmt->execute();
    }

    /**
     * Czyści nieudane próby logowania po udanym logowaniu
     */
    private function clearFailedAttempts(string $email, string $ip): void {
        $database = new Database();
        $conn = $database->connect();
        
        $stmt = $conn->prepare(
            'DELETE FROM login_attempts 
             WHERE (email = :email OR ip_address = :ip) 
             AND success = FALSE'
        );
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Czyści stare wpisy z tabeli login_attempts (do wywołania np. przez cron)
     */
    public static function cleanupOldLoginAttempts(): int {
        $database = new Database();
        $conn = $database->connect();
        
        $stmt = $conn->prepare(
            'DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL \'24 hours\''
        );
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
