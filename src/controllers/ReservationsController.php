<?php

require_once __DIR__ . '/../repository/ReservationRepository.php';
require_once __DIR__ . '/../repository/ReviewRepository.php';
require_once __DIR__ . '/../repository/WorkerRepository.php';
require_once __DIR__ . '/../repository/ServiceRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/SecurityController.php';

class ReservationsController {
    private ReservationRepository $reservationRepo;
    private ReviewRepository $reviewRepo;
    private WorkerRepository $workerRepo;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->reservationRepo = new ReservationRepository();
        $this->reviewRepo = new ReviewRepository();
        $this->workerRepo = new WorkerRepository();
    }

    public function index() {
        SecurityController::requireAuth();
        $user = SecurityController::getCurrentUser();
        
        $tab = $_GET['tab'] ?? 'current';
        
        if ($user['role'] === 'worker') {
            $workerRepo = new WorkerRepository();
            $worker = $workerRepo->findByUserId($user['id']);
            
            if ($worker) {
                $currentReservations = $this->reservationRepo->findByWorkerId($worker['id'], 'current');
                $historyReservations = $this->reservationRepo->findByWorkerId($worker['id'], 'history');
            } else {
                $currentReservations = [];
                $historyReservations = [];
            }
            $isWorkerView = true;
        } else {
            $currentReservations = $this->reservationRepo->findByClientId($user['id'], 'current');
            $historyReservations = $this->reservationRepo->findByClientId($user['id'], 'history');
            $isWorkerView = false;
        }
        
        foreach ($historyReservations as &$reservation) {
            $reservation['can_review'] = !$isWorkerView && 
                                         $reservation['status'] === 'completed' && 
                                         !$this->reservationRepo->hasReviewForReservation($reservation['id']);
        }
        
        include __DIR__ . '/../../public/views/reservations.php';
    }

    public function book() {
        SecurityController::requireAuth();
        $user = SecurityController::getCurrentUser();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit();
        }
        
        $workerId = (int) ($_POST['worker_id'] ?? 0);
        $serviceId = !empty($_POST['service_id']) ? (int) $_POST['service_id'] : null;
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        
        if (!$workerId || !$date || !$time) {
            $_SESSION['booking_error'] = 'Proszę wypełnić wszystkie wymagane pola.';
            header('Location: /worker/' . $workerId);
            exit();
        }
        
        $worker = $this->workerRepo->findById($workerId);
        if ($worker && $worker['user_id'] == $user['id']) {
            $_SESSION['booking_error'] = 'Nie możesz zarezerwować wizyty u siebie.';
            header('Location: /worker/' . $workerId);
            exit();
        }
        
        $reservationDate = new DateTime($date);
        $today = new DateTime('today');
        if ($reservationDate < $today) {
            $_SESSION['booking_error'] = 'Nie można zarezerwować terminu w przeszłości.';
            header('Location: /worker/' . $workerId);
            exit();
        }
        
        if ($reservationDate == $today) {
            $reservationTime = new DateTime($date . ' ' . $time);
            $now = new DateTime();
            if ($reservationTime <= $now) {
                $_SESSION['booking_error'] = 'Nie można zarezerwować terminu w przeszłości. Wybierz późniejszą godzinę.';
                header('Location: /worker/' . $workerId);
                exit();
            }
        }
        
        if (!$this->reservationRepo->isTimeSlotAvailable($workerId, $date, $time)) {
            $_SESSION['booking_error'] = 'Ten termin jest już zajęty. Proszę wybrać inny.';
            header('Location: /worker/' . $workerId);
            exit();
        }
        
        if ($this->reservationRepo->create($workerId, $user['id'], $serviceId, $date, $time, $notes)) {
            $_SESSION['booking_success'] = 'Rezerwacja została utworzona pomyślnie!';
            header('Location: /reservations');
        } else {
            $_SESSION['booking_error'] = 'Wystąpił błąd podczas tworzenia rezerwacji.';
            header('Location: /worker/' . $workerId);
        }
        exit();
    }

    public function cancel() {
        SecurityController::requireAuth();
        $user = SecurityController::getCurrentUser();
        
        $reservationId = (int) ($_GET['id'] ?? 0);
        
        if (!$reservationId) {
            header('Location: /reservations');
            exit();
        }
        
        $reservation = $this->reservationRepo->findById($reservationId);
        
        if (!$reservation || ($reservation['client_id'] != $user['id'] && 
            ($user['role'] !== 'worker' || $reservation['worker_id'] != $this->getWorkerIdForUser($user['id'])))) {
            $_SESSION['reservation_error'] = 'Nie masz uprawnień do anulowania tej rezerwacji.';
            header('Location: /reservations');
            exit();
        }
        
        if ($this->reservationRepo->cancel($reservationId)) {
            $_SESSION['reservation_success'] = 'Rezerwacja została anulowana.';
        } else {
            $_SESSION['reservation_error'] = 'Nie udało się anulować rezerwacji.';
        }
        
        header('Location: /reservations');
        exit();
    }

    public function confirm() {
        SecurityController::requireAuth();
        $user = SecurityController::getCurrentUser();
        
        if ($user['role'] !== 'worker') {
            header('Location: /reservations');
            exit();
        }
        
        $reservationId = (int) ($_GET['id'] ?? 0);
        $reservation = $this->reservationRepo->findById($reservationId);
        
        $workerId = $this->getWorkerIdForUser($user['id']);
        
        if (!$reservation || $reservation['worker_id'] != $workerId) {
            $_SESSION['reservation_error'] = 'Nie masz uprawnień do potwierdzenia tej rezerwacji.';
            header('Location: /reservations');
            exit();
        }
        
        if ($this->reservationRepo->confirm($reservationId)) {
            $_SESSION['reservation_success'] = 'Rezerwacja została potwierdzona.';
        } else {
            $_SESSION['reservation_error'] = 'Nie udało się potwierdzić rezerwacji.';
        }
        
        header('Location: /reservations');
        exit();
    }

    public function complete() {
        SecurityController::requireAuth();
        $user = SecurityController::getCurrentUser();
        
        if ($user['role'] !== 'worker') {
            header('Location: /reservations');
            exit();
        }
        
        $reservationId = (int) ($_GET['id'] ?? 0);
        $reservation = $this->reservationRepo->findById($reservationId);
        
        $workerId = $this->getWorkerIdForUser($user['id']);
        
        if (!$reservation || $reservation['worker_id'] != $workerId) {
            $_SESSION['reservation_error'] = 'Nie masz uprawnień do oznaczenia tej rezerwacji jako wykonanej.';
            header('Location: /reservations');
            exit();
        }
        
        if ($this->reservationRepo->complete($reservationId)) {
            $_SESSION['reservation_success'] = 'Rezerwacja została oznaczona jako wykonana.';
        } else {
            $_SESSION['reservation_error'] = 'Nie udało się oznaczyć rezerwacji jako wykonanej.';
        }
        
        header('Location: /reservations');
        exit();
    }

    public function review() {
        SecurityController::requireAuth();
        $user = SecurityController::getCurrentUser();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /reservations');
            exit();
        }
        
        $reservationId = (int) ($_POST['reservation_id'] ?? 0);
        $rating = (int) ($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        
        if (!$reservationId || $rating < 1 || $rating > 5) {
            $_SESSION['reservation_error'] = 'Proszę podać poprawną ocenę (1-5).';
            header('Location: /reservations?tab=history');
            exit();
        }
        
        $reservation = $this->reservationRepo->findById($reservationId);
        
        if (!$reservation || $reservation['client_id'] != $user['id']) {
            $_SESSION['reservation_error'] = 'Nie masz uprawnień do oceny tej rezerwacji.';
            header('Location: /reservations?tab=history');
            exit();
        }
        
        if ($reservation['status'] !== 'completed') {
            $_SESSION['reservation_error'] = 'Możesz ocenić tylko zakończone rezerwacje.';
            header('Location: /reservations?tab=history');
            exit();
        }
        
        if ($this->reservationRepo->hasReviewForReservation($reservationId)) {
            $_SESSION['reservation_error'] = 'Ta rezerwacja została już oceniona.';
            header('Location: /reservations?tab=history');
            exit();
        }
        
        $reviewId = $this->reviewRepo->create(
            $reservation['worker_id'],
            $user['id'],
            $reservationId,
            $rating,
            $comment
        );
        
        if ($reviewId) {
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $uploadDir = __DIR__ . '/../../uploads/reviews/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                        $filename = uniqid('review_') . '.' . $ext;
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadDir . $filename)) {
                            $this->reviewRepo->addImage($reviewId, $filename);
                        }
                    }
                }
            }
            
            $this->reviewRepo->updateWorkerRating($reservation['worker_id']);
            
            $_SESSION['reservation_success'] = 'Dziękujemy za Twoją opinię!';
        } else {
            $_SESSION['reservation_error'] = 'Nie udało się dodać opinii.';
        }
        
        header('Location: /reservations?tab=history');
        exit();
    }

    private function getWorkerIdForUser(int $userId): ?int {
        $worker = $this->workerRepo->findByUserId($userId);
        return $worker ? (int) $worker['id'] : null;
    }
}

