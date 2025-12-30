<?php

require_once __DIR__ . '/../../Database.php';

class ReviewRepository {
    private PDO $database;

    public function __construct() {
        $this->database = (new Database())->connect();
    }

    public function create(int $workerId, int $clientId, ?int $reservationId, int $rating, string $comment): int {
        $stmt = $this->database->prepare('
            INSERT INTO reviews (worker_id, client_id, reservation_id, rating, comment)
            VALUES (:worker_id, :client_id, :reservation_id, :rating, :comment)
        ');
        
        $stmt->bindParam(':worker_id', $workerId, PDO::PARAM_INT);
        $stmt->bindParam(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->bindParam(':reservation_id', $reservationId, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment);
        
        $stmt->execute();
        return (int) $this->database->lastInsertId();
    }

    public function addImage(int $reviewId, string $imagePath): bool {
        $stmt = $this->database->prepare('
            INSERT INTO review_images (review_id, image_path)
            VALUES (:review_id, :image_path)
        ');
        $stmt->bindParam(':review_id', $reviewId, PDO::PARAM_INT);
        $stmt->bindParam(':image_path', $imagePath);
        return $stmt->execute();
    }

    public function findByWorkerId(int $workerId, int $limit = 10, int $offset = 0): array {
        $stmt = $this->database->prepare('
            SELECT r.*, 
                   u.name as client_name,
                   u.profile_image as client_image
            FROM reviews r
            JOIN users u ON r.client_id = u.id
            WHERE r.worker_id = :worker_id
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindParam(':worker_id', $workerId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($reviews as &$review) {
            $review['images'] = $this->getReviewImages($review['id']);
        }
        
        return $reviews;
    }

    public function getReviewImages(int $reviewId): array {
        $stmt = $this->database->prepare('
            SELECT * FROM review_images WHERE review_id = :review_id
        ');
        $stmt->bindParam(':review_id', $reviewId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByWorkerId(int $workerId): int {
        $stmt = $this->database->prepare('
            SELECT COUNT(*) FROM reviews WHERE worker_id = :worker_id
        ');
        $stmt->bindParam(':worker_id', $workerId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getAverageRating(int $workerId): float {
        $stmt = $this->database->prepare('
            SELECT AVG(rating) FROM reviews WHERE worker_id = :worker_id
        ');
        $stmt->bindParam(':worker_id', $workerId, PDO::PARAM_INT);
        $stmt->execute();
        $avg = $stmt->fetchColumn();
        return $avg ? round((float) $avg, 1) : 0;
    }

    public function updateWorkerRating(int $workerId): bool {
        $avgRating = $this->getAverageRating($workerId);
        $count = $this->countByWorkerId($workerId);
        
        $stmt = $this->database->prepare('
            UPDATE workers SET rating = :rating, reviews_count = :count WHERE id = :worker_id
        ');
        $stmt->bindParam(':rating', $avgRating);
        $stmt->bindParam(':count', $count, PDO::PARAM_INT);
        $stmt->bindParam(':worker_id', $workerId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findById(int $id): ?array {
        $stmt = $this->database->prepare('
            SELECT r.*, 
                   u.name as client_name,
                   u.profile_image as client_image
            FROM reviews r
            JOIN users u ON r.client_id = u.id
            WHERE r.id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $review = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($review) {
            $review['images'] = $this->getReviewImages($review['id']);
        }
        
        return $review ?: null;
    }

    public function delete(int $id): bool {
        $stmt = $this->database->prepare('DELETE FROM review_images WHERE review_id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $stmt = $this->database->prepare('DELETE FROM reviews WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

