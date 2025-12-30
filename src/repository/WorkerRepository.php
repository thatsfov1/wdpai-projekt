<?php

require_once __DIR__ . '/../../Database.php';

class WorkerRepository {
    private PDO $database;

    public function __construct() {
        $this->database = (new Database())->connect();
    }

    public function create(int $userId, int $categoryId, string $description, int $experience): bool {
        $stmt = $this->database->prepare(
            'INSERT INTO workers (user_id, category_id, description, experience_years) 
             VALUES (:user_id, :category_id, :description, :experience)'
        );

        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':experience', $experience, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function findByUserId(int $userId): ?array {
        $stmt = $this->database->prepare(
            'SELECT w.*, c.name as category_name, c.slug as category_slug 
             FROM workers w 
             JOIN categories c ON w.category_id = c.id 
             WHERE w.user_id = :user_id'
        );
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findByCategorySlug(string $slug, ?string $city = null, ?string $sort = null): array {
        return $this->findByCategorySlugWithFilters($slug, $city, $sort);
    }

    public function findByCategorySlugWithFilters(
        string $slug, 
        ?string $city = null, 
        ?string $sort = null,
        ?float $minRating = null,
        ?int $minExperience = null,
        ?float $priceFrom = null,
        ?float $priceTo = null
    ): array {
        $sql = 'SELECT w.*, u.name, u.city, u.profile_image, c.name as category_name 
                FROM workers w 
                JOIN users u ON w.user_id = u.id 
                JOIN categories c ON w.category_id = c.id 
                WHERE c.slug = :slug';
        
        $params = [':slug' => $slug];

        if ($city) {
            $sql .= ' AND LOWER(u.city) LIKE LOWER(:city)';
            $params[':city'] = '%' . $city . '%';
        }

        if ($minRating !== null) {
            $sql .= ' AND w.rating >= :min_rating';
            $params[':min_rating'] = $minRating;
        }

        if ($minExperience !== null) {
            $sql .= ' AND w.experience_years >= :min_experience';
            $params[':min_experience'] = $minExperience;
        }

        if ($priceFrom !== null) {
            $sql .= ' AND w.hourly_rate >= :price_from';
            $params[':price_from'] = $priceFrom;
        }

        if ($priceTo !== null) {
            $sql .= ' AND w.hourly_rate <= :price_to';
            $params[':price_to'] = $priceTo;
        }

        switch ($sort) {
            case 'rating':
                $sql .= ' ORDER BY w.rating DESC';
                break;
            case 'experience':
                $sql .= ' ORDER BY w.experience_years DESC';
                break;
            case 'price_low':
                $sql .= ' ORDER BY w.hourly_rate ASC NULLS LAST';
                break;
            case 'price_high':
                $sql .= ' ORDER BY w.hourly_rate DESC NULLS LAST';
                break;
            case 'reviews':
                $sql .= ' ORDER BY w.reviews_count DESC';
                break;
            default:
                $sql .= ' ORDER BY w.rating DESC, w.reviews_count DESC';
        }

        $stmt = $this->database->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $stmt = $this->database->prepare(
            'SELECT w.*, u.name, u.email, u.city, u.phone, u.profile_image, u.created_at as user_created_at,
                    c.name as category_name, c.slug as category_slug
             FROM workers w 
             JOIN users u ON w.user_id = u.id 
             JOIN categories c ON w.category_id = c.id 
             WHERE w.id = :id'
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function update(int $workerId, int $categoryId, string $description, int $experience, ?float $hourlyRate): bool {
        $stmt = $this->database->prepare(
            'UPDATE workers SET category_id = :category_id, description = :description, 
             experience_years = :experience, hourly_rate = :hourly_rate WHERE id = :id'
        );

        $stmt->bindParam(':id', $workerId, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':experience', $experience, PDO::PARAM_INT);
        $stmt->bindParam(':hourly_rate', $hourlyRate);

        return $stmt->execute();
    }

    public function getFeatured(int $limit = 4): array {
        $stmt = $this->database->prepare(
            'SELECT w.*, u.name, u.city, u.profile_image, c.name as category_name 
             FROM workers w 
             JOIN users u ON w.user_id = u.id 
             JOIN categories c ON w.category_id = c.id 
             ORDER BY w.rating DESC, w.reviews_count DESC 
             LIMIT :limit'
        );
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search(string $query, ?string $city = null): array {
        $sql = 'SELECT w.*, u.name, u.city, u.profile_image, c.name as category_name 
                FROM workers w 
                JOIN users u ON w.user_id = u.id 
                JOIN categories c ON w.category_id = c.id 
                WHERE 1=1';
        
        $params = [];

        if (!empty($query)) {
            $sql .= ' AND (LOWER(u.name) LIKE LOWER(:query) 
                       OR LOWER(c.name) LIKE LOWER(:query) 
                       OR LOWER(w.description) LIKE LOWER(:query))';
            $params[':query'] = '%' . $query . '%';
        }

        if ($city) {
            $sql .= ' AND LOWER(u.city) LIKE LOWER(:city)';
            $params[':city'] = '%' . $city . '%';
        }

        $sql .= ' ORDER BY w.rating DESC';

        $stmt = $this->database->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

