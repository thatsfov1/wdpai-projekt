<?php

require_once __DIR__ . '/../../Database.php';

class CategoryRepository {
    private PDO $database;

    public function __construct() {
        $this->database = (new Database())->connect();
    }

    public function findAll(): array {
        $stmt = $this->database->query('SELECT * FROM categories ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $stmt = $this->database->prepare('SELECT * FROM categories WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findBySlug(string $slug): ?array {
        $stmt = $this->database->prepare('SELECT * FROM categories WHERE slug = :slug');
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}

