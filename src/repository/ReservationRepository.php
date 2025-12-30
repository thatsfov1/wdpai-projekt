<?php

require_once __DIR__ . '/../../Database.php';

class ReservationRepository {
    private PDO $database;

    public function __construct() {
        $this->database = (new Database())->connect();
    }

    public function create(int $workerId, int $clientId, ?int $serviceId, string $date, string $time, ?string $notes = null): bool {
        $stmt = $this->database->prepare('
            INSERT INTO reservations (worker_id, client_id, service_id, reservation_date, reservation_time, notes)
            VALUES (:worker_id, :client_id, :service_id, :reservation_date, :reservation_time, :notes)
        ');
        
        $stmt->bindParam(':worker_id', $workerId, PDO::PARAM_INT);
        $stmt->bindParam(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $stmt->bindParam(':reservation_date', $date);
        $stmt->bindParam(':reservation_time', $time);
        $stmt->bindParam(':notes', $notes);
        
        return $stmt->execute();
    }

    public function getLastInsertId(): int {
        return (int) $this->database->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->database->prepare('
            SELECT r.*, 
                   w.id as worker_id,
                   u.name as worker_name,
                   u.profile_image as worker_image,
                   u.phone as worker_phone,
                   c.name as category_name,
                   s.name as service_name,
                   s.price as service_price,
                   cu.name as client_name,
                   cu.phone as client_phone
            FROM reservations r
            JOIN workers w ON r.worker_id = w.id
            JOIN users u ON w.user_id = u.id
            JOIN categories c ON w.category_id = c.id
            LEFT JOIN services s ON r.service_id = s.id
            JOIN users cu ON r.client_id = cu.id
            WHERE r.id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findByClientId(int $clientId, string $status = 'all'): array {
        $query = '
            SELECT DISTINCT r.*, 
                   w.id as worker_id,
                   u.name as worker_name,
                   u.profile_image as worker_image,
                   c.name as category_name,
                   s.name as service_name,
                   s.price as service_price
            FROM reservations r
            JOIN workers w ON r.worker_id = w.id
            JOIN users u ON w.user_id = u.id
            JOIN categories c ON w.category_id = c.id
            LEFT JOIN services s ON r.service_id = s.id
            WHERE r.client_id = :client_id
        ';
        
        if ($status === 'current') {
            $query .= " AND r.status IN ('pending', 'confirmed')";
        } elseif ($status === 'history') {
            $query .= " AND r.status IN ('completed', 'cancelled')";
        }
        
        $query .= ' ORDER BY r.reservation_date DESC, r.reservation_time DESC';
        
        $stmt = $this->database->prepare($query);
        $stmt->bindParam(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByWorkerId(int $workerId, string $status = 'all'): array {
        $query = '
            SELECT DISTINCT r.*, 
                   cu.name as client_name,
                   cu.phone as client_phone,
                   cu.profile_image as client_image,
                   s.name as service_name,
                   s.price as service_price
            FROM reservations r
            JOIN users cu ON r.client_id = cu.id
            LEFT JOIN services s ON r.service_id = s.id
            WHERE r.worker_id = :worker_id
        ';
        
        if ($status === 'current') {
            $query .= " AND r.status IN ('pending', 'confirmed')";
        } elseif ($status === 'history') {
            $query .= " AND r.status IN ('completed', 'cancelled')";
        }
        
        $query .= ' ORDER BY r.reservation_date DESC, r.reservation_time DESC';
        
        $stmt = $this->database->prepare($query);
        $stmt->bindParam(':worker_id', $workerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->database->prepare('
            UPDATE reservations 
            SET status = :status, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function cancel(int $id): bool {
        return $this->updateStatus($id, 'cancelled');
    }

    public function confirm(int $id): bool {
        return $this->updateStatus($id, 'confirmed');
    }

    public function complete(int $id): bool {
        return $this->updateStatus($id, 'completed');
    }

    public function isTimeSlotAvailable(int $workerId, string $date, string $time): bool {
        $stmt = $this->database->prepare('
            SELECT COUNT(*) FROM reservations 
            WHERE worker_id = :worker_id 
            AND reservation_date = :date 
            AND reservation_time = :time
            AND status NOT IN (\'cancelled\')
        ');
        $stmt->bindParam(':worker_id', $workerId, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->execute();
        return $stmt->fetchColumn() == 0;
    }

    public function hasReviewForReservation(int $reservationId): bool {
        $stmt = $this->database->prepare('
            SELECT COUNT(*) FROM reviews WHERE reservation_id = :reservation_id
        ');
        $stmt->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}

