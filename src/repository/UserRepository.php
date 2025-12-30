<?php

require_once __DIR__ . '/../../Database.php';
require_once __DIR__ . '/../model/User.php';

class UserRepository {
    private PDO $database;

    public function __construct() {
        $this->database = (new Database())->connect();
    }

    public function findByEmail(string $email): ?User {
        $stmt = $this->database->prepare(
            'SELECT * FROM users WHERE email = :email'
        );
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    public function findById(int $id): ?User {
        $stmt = $this->database->prepare(
            'SELECT * FROM users WHERE id = :id'
        );
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapRowToUser($row);
    }

    public function create(User $user): bool {
        $stmt = $this->database->prepare(
            'INSERT INTO users (email, password, name, role, phone, city) 
             VALUES (:email, :password, :name, :role, :phone, :city)'
        );

        return $this->executeUserInsert($stmt, $user);
    }

    public function createAndGetId(User $user): ?int {
        $stmt = $this->database->prepare(
            'INSERT INTO users (email, password, name, role, phone, city) 
             VALUES (:email, :password, :name, :role, :phone, :city)
             RETURNING id'
        );

        $this->executeUserInsert($stmt, $user);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['id'] : null;
    }

    private function executeUserInsert(PDOStatement $stmt, User $user): bool {
        $email = $user->getEmail();
        $password = $user->getPassword();
        $name = $user->getName();
        $role = $user->getRole();
        $phone = $user->getPhone();
        $city = $user->getCity();

        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':city', $city, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function update(User $user): bool {
        $stmt = $this->database->prepare(
            'UPDATE users SET name = :name, phone = :phone, city = :city, 
             profile_image = :profile_image WHERE id = :id'
        );

        $id = $user->getId();
        $name = $user->getName();
        $phone = $user->getPhone();
        $city = $user->getCity();
        $profileImage = $user->getProfileImage();

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':city', $city, PDO::PARAM_STR);
        $stmt->bindParam(':profile_image', $profileImage, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function emailExists(string $email): bool {
        $stmt = $this->database->prepare(
            'SELECT COUNT(*) FROM users WHERE email = :email'
        );
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    private function mapRowToUser(array $row): User {
        $user = new User(
            $row['email'],
            $row['password'],
            $row['name'],
            $row['role'],
            $row['id'],
            $row['created_at']
        );
        $user->setPhone($row['phone'] ?? null);
        $user->setCity($row['city'] ?? null);
        $user->setProfileImage($row['profile_image'] ?? null);
        
        return $user;
    }
}
