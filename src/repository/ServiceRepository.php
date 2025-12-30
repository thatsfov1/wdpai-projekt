<?php

require_once __DIR__ . '/../../Database.php';

class ServiceRepository {
    private PDO $database;

    public function __construct() {
        $this->database = (new Database())->connect();
    }

    public function findByWorkerId(int $workerId): array {
        $stmt = $this->database->prepare(
            'SELECT * FROM services WHERE worker_id = :worker_id ORDER BY created_at DESC'
        );
        $stmt->bindParam(':worker_id', $workerId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(int $workerId, string $name, string $description, ?float $price): bool {
        $stmt = $this->database->prepare(
            'INSERT INTO services (worker_id, name, description, price) 
             VALUES (:worker_id, :name, :description, :price)'
        );

        $stmt->bindParam(':worker_id', $workerId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price);

        return $stmt->execute();
    }

    public function update(int $id, string $name, string $description, ?float $price): bool {
        $stmt = $this->database->prepare(
            'UPDATE services SET name = :name, description = :description, price = :price WHERE id = :id'
        );

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price);

        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->database->prepare('DELETE FROM services WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findById(int $id): ?array {
        $stmt = $this->database->prepare('SELECT * FROM services WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}

