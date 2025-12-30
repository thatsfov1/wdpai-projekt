<?php

class User {
    private ?int $id;
    private string $email;
    private string $password;
    private string $name;
    private string $role;
    private ?string $phone;
    private ?string $city;
    private ?string $profileImage;
    private ?string $createdAt;

    public function __construct(
        string $email,
        string $password,
        string $name,
        string $role,
        ?int $id = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->role = $role;
        $this->phone = null;
        $this->city = null;
        $this->profileImage = null;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function setRole(string $role): void {
        $this->role = $role;
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function setPhone(?string $phone): void {
        $this->phone = $phone;
    }

    public function getCity(): ?string {
        return $this->city;
    }

    public function setCity(?string $city): void {
        $this->city = $city;
    }

    public function getProfileImage(): ?string {
        return $this->profileImage;
    }

    public function setProfileImage(?string $profileImage): void {
        $this->profileImage = $profileImage;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function isWorker(): bool {
        return $this->role === 'worker';
    }

    public function isClient(): bool {
        return $this->role === 'client';
    }

    public function getProfileImageUrl(): string {
        if ($this->profileImage) {
            return '/uploads/profiles/' . $this->profileImage;
        }
        return '/public/images/default-avatar.svg';
    }
}
