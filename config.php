<?php
/**
 * Configuration de la base de données
 * Gère la connexion PDO à MySQL
 */
class Config {
    /**
     * Retourne une instance PDO pour la connexion à la base de données
     * @return PDO
     */
    public static function getConnexion() {
        try {
            $pdo = new PDO(
                'mysql:host=localhost;dbname=SportFuel;charset=utf8',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            return $pdo;
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
}
?>
