<?php

require_once __DIR__ . "/config.php";

class Database {
    private string $username;
    private string $password;
    private string $host;
    private string $database;
    private string $port;
    private static ?PDO $connection = null;

    public function __construct()
    {
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->host = HOST;
        $this->database = DATABASE;
        $this->port = PORT;
    }

    public function connect(): PDO
    {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    "pgsql:host={$this->host};port={$this->port};dbname={$this->database}",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                
                throw new Exception("Unable to connect to database. Please try again later.");
            }
        }
        return self::$connection;
    }
}