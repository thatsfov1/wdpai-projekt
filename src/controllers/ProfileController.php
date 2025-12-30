<?php

require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/WorkerRepository.php';
require_once __DIR__ . '/../repository/ServiceRepository.php';
require_once __DIR__ . '/../repository/CategoryRepository.php';
require_once __DIR__ . '/SecurityController.php';

class ProfileController {
    private array $messages = [];

    public function index() {
        SecurityController::requireAuth();
        
        $userRepo = new UserRepository();
        $user = $userRepo->findById($_SESSION['user_id']);
        
        $worker = null;
        $services = [];
        $categories = [];
        
        if ($user->isWorker()) {
            $workerRepo = new WorkerRepository();
            $serviceRepo = new ServiceRepository();
            $categoryRepo = new CategoryRepository();
            
            $worker = $workerRepo->findByUserId($user->getId());
            if ($worker) {
                $services = $serviceRepo->findByWorkerId($worker['id']);
            }
            $categories = $categoryRepo->findAll();
        }

        $messages = $this->messages;
        include __DIR__ . '/../../public/views/profile.php';
    }

    public function update() {
        SecurityController::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /profile');
            exit();
        }

        $userRepo = new UserRepository();
        $user = $userRepo->findById($_SESSION['user_id']);

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city = trim($_POST['city'] ?? '');

        if (mb_strlen($name) < 2) {
            $_SESSION['profile_error'] = 'Imię musi mieć minimum 2 znaki';
            header('Location: /profile');
            exit();
        }

        if (!empty($phone) && !preg_match('/^\+48\s?\d{3}\s?\d{3}\s?\d{3}$/', $phone) && !preg_match('/^\+48\d{9}$/', $phone)) {
            $_SESSION['profile_error'] = 'Numer telefonu musi zaczynać się od +48 i mieć 9 cyfr (np. +48 123 456 789)';
            header('Location: /profile');
            exit();
        }

        $user->setName(htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
        $user->setPhone(htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'));
        $user->setCity(htmlspecialchars($city, ENT_QUOTES, 'UTF-8'));

        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleProfileImageUpload($_FILES['profile_image']);
            if ($uploadResult['success']) {
                $user->setProfileImage($uploadResult['filename']);
            }
        }

        $userRepo->update($user);
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['profile_success'] = 'Profil został zaktualizowany';

        if ($user->isWorker()) {
            $workerRepo = new WorkerRepository();
            $worker = $workerRepo->findByUserId($user->getId());
            
            if ($worker) {
                $categoryId = (int)($_POST['category_id'] ?? $worker['category_id']);
                $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
                $experience = (int)($_POST['experience'] ?? 0);
                $hourlyRate = !empty($_POST['hourly_rate']) ? (float)$_POST['hourly_rate'] : null;
                
                $workerRepo->update($worker['id'], $categoryId, $description, $experience, $hourlyRate);
            }
        }

        header('Location: /profile');
        exit();
    }

    public function addService() {
        SecurityController::requireRole('worker');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /profile');
            exit();
        }

        $workerRepo = new WorkerRepository();
        $worker = $workerRepo->findByUserId($_SESSION['user_id']);
        
        if (!$worker) {
            header('Location: /profile');
            exit();
        }

        $name = htmlspecialchars(trim($_POST['service_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars(trim($_POST['service_description'] ?? ''), ENT_QUOTES, 'UTF-8');
        $price = !empty($_POST['service_price']) ? (float)$_POST['service_price'] : null;

        if (empty($name)) {
            $_SESSION['profile_error'] = 'Nazwa usługi jest wymagana';
            header('Location: /profile');
            exit();
        }

        $serviceRepo = new ServiceRepository();
        $serviceRepo->create($worker['id'], $name, $description, $price);
        
        $_SESSION['profile_success'] = 'Usługa została dodana';
        header('Location: /profile');
        exit();
    }

    public function deleteService() {
        SecurityController::requireRole('worker');
        
        $serviceId = (int)($_GET['id'] ?? 0);
        if (!$serviceId) {
            header('Location: /profile');
            exit();
        }

        $serviceRepo = new ServiceRepository();
        $service = $serviceRepo->findById($serviceId);
        
        if ($service) {
            $workerRepo = new WorkerRepository();
            $worker = $workerRepo->findByUserId($_SESSION['user_id']);
            
            if ($worker && $service['worker_id'] === $worker['id']) {
                $serviceRepo->delete($serviceId);
                $_SESSION['profile_success'] = 'Usługa została usunięta';
            }
        }

        header('Location: /profile');
        exit();
    }

    private function handleProfileImageUpload(array $file): array {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Dozwolone formaty: JPG, PNG, WebP'];
        }

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'Maksymalny rozmiar pliku to 5MB'];
        }

        $uploadDir = __DIR__ . '/../../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_') . '.' . $extension;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'filename' => $filename];
        }

        return ['success' => false, 'error' => 'Błąd podczas przesyłania pliku'];
    }
}

