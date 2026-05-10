<?php

class Database {
    private $host = 'localhost';
    private $db_name = 'sportfuel';
    private $user = 'root';
    private $password = '';
    private $pdo;

    public function connect() {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        try {
            $this->pdo = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8',
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            return $this->pdo;
        } catch (PDOException $e) {
            throw new Exception('Erreur de connexion: ' . $e->getMessage());
        }
    }

    public function getPDO() {
        return $this->connect();
    }
}

// Keep backward compatibility for controllers that expect a global $pdo variable.
try {
    $database = new Database();
    $pdo = $database->getPDO();
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}
