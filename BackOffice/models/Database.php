<?php
class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            $host = '127.0.0.1';
            $dbname = 'sportfuel';
            $username = 'root';
            $password = '';
            $port = '3306';

            try {
                self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4", $username, $password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::ensureSchema(self::$pdo);
            } catch (PDOException $e) {
                if ($e->getCode() === '1049') {
                    self::createDatabaseStructure($host, $dbname, $username, $password, $port);
                    self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4", $username, $password);
                    self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    self::ensureSchema(self::$pdo);
                } else {
                    die("Erreur de connexion à la base de données: " . $e->getMessage());
                }
            }
        }
        return self::$pdo;
    }

    private static function ensureSchema(PDO $pdo) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `user` (
            id_user INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(50),
            prenom VARCHAR(50),
            email VARCHAR(100) UNIQUE,
            password VARCHAR(255)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `publication` (
            id_pub INT AUTO_INCREMENT PRIMARY KEY,
            id_user INT,
            text TEXT,
            date DATETIME,
            FOREIGN KEY (id_user) REFERENCES `user`(id_user)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $stmtPublicationPriorite = $pdo->query("SHOW COLUMNS FROM `publication` LIKE 'priorite'");
        if (!$stmtPublicationPriorite->fetch()) {
            $pdo->exec("ALTER TABLE `publication` ADD COLUMN priorite VARCHAR(20) NOT NULL DEFAULT 'normal' AFTER text");
        }

        $stmtPublicationPriorityScore = $pdo->query("SHOW COLUMNS FROM `publication` LIKE 'priority_score'");
        if (!$stmtPublicationPriorityScore->fetch()) {
            $pdo->exec("ALTER TABLE `publication` ADD COLUMN priority_score INT NOT NULL DEFAULT 30 AFTER priorite");
        }

        $stmtPublicationStatut = $pdo->query("SHOW COLUMNS FROM `publication` LIKE 'statut'");
        if (!$stmtPublicationStatut->fetch()) {
            $pdo->exec("ALTER TABLE `publication` ADD COLUMN statut VARCHAR(20) NOT NULL DEFAULT 'En attente' AFTER priority_score");
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS `commentaire` (
            id_cmmnt INT AUTO_INCREMENT PRIMARY KEY,
            id_pub INT,
            text TEXT,
            date DATETIME,
            id_user INT,
            FOREIGN KEY (id_pub) REFERENCES `publication`(id_pub)
                ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (id_user) REFERENCES `user`(id_user)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $stmt = $pdo->query("SHOW COLUMNS FROM `commentaire` LIKE 'id_user'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE `commentaire` ADD COLUMN id_user INT DEFAULT 1 AFTER id_pub");
        }

        $countUsers = (int) $pdo->query("SELECT COUNT(*) FROM `user`")->fetchColumn();
        if ($countUsers === 0) {
            $pdo->exec("INSERT IGNORE INTO `user` (id_user, nom, prenom, email, password) VALUES
                (1, 'Doe', 'John', 'john@example.com', 'password'),
                (2, 'Smith', 'Jane', 'jane@example.com', 'password'),
                (3, 'Sportif', 'User', 'sportif@example.com', 'password')");
        }

        $countPub = (int) $pdo->query("SELECT COUNT(*) FROM `publication`")->fetchColumn();
        if ($countPub === 0) {
            $pdo->exec("INSERT IGNORE INTO `publication` (id_pub, id_user, text, date) VALUES
                (1, 1, 'Première publication', '2023-01-01 10:00:00'),
                (2, 2, 'Deuxième publication', '2023-01-02 11:00:00'),
                (3, 3, 'Publication du sportif', '2023-01-03 12:00:00')");
        }

        $countComments = (int) $pdo->query("SELECT COUNT(*) FROM `commentaire`")->fetchColumn();
        if ($countComments === 0) {
            $pdo->exec("INSERT IGNORE INTO `commentaire` (id_cmmnt, id_pub, id_user, text, date) VALUES
                (1, 1, 2, 'Commentaire sur la première', '2023-01-01 10:30:00'),
                (2, 2, 1, 'Commentaire sur la deuxième', '2023-01-02 11:30:00')");
        }
    }

    private static function createDatabaseStructure($host, $dbname, $username, $password, $port) {
        try {
            $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");
            self::ensureSchema($pdo);
        } catch (PDOException $e) {
            die("Erreur de création de la base et des tables: " . $e->getMessage());
        }
    }
}
?>