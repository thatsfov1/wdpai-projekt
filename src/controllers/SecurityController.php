<?php

require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityController {
    
    private UserRepository $userRepository;
    private array $messages = [];

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

            $errors = $this->validateLogin($email, $password);

            if (empty($errors)) {
                $user = $this->userRepository->findByEmail($email);

                if ($user && password_verify($password, $user->getPassword())) {
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['user_email'] = $user->getEmail();
                    $_SESSION['user_name'] = $user->getName();
                    $_SESSION['user_role'] = $user->getRole();

                    header('Location: /');
                    exit();
                } else {
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
                    $this->messages[] = [
                        'type' => 'error',
                        'text' => 'Użytkownik z tym adresem email już istnieje'
                    ];
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

        if (empty($name)) {
            $errors[] = 'Imię i nazwisko jest wymagane';
        } elseif (mb_strlen($name) < 2) {
            $errors[] = 'Imię i nazwisko musi zawierać minimum 2 znaki';
        } elseif (!preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ\s\-]+$/u', $name)) {
            $errors[] = 'Imię i nazwisko może zawierać tylko litery, spacje i myślniki';
        }

        if (empty($email)) {
            $errors[] = 'Email jest wymagany';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Wprowadź poprawny adres email';
        }

        if (empty($password)) {
            $errors[] = 'Hasło jest wymagane';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Hasło musi zawierać minimum 6 znaków';
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
}
