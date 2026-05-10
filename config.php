<?php
$host = '127.0.0.1';
$dbname = 'sportfuel';
$username = 'root';
$password = '';
$port = '3306';

class Config {
    public static function getConnexion() {
        global $host, $dbname, $username, $password, $port;

        try {
            return new PDO(
                "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            die('Erreur de connexion a la base de donnees : ' . $e->getMessage());
        }
    }
}
