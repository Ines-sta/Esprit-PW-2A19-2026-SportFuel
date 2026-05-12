<?php
require_once __DIR__ . '/../../config.php';

class SocialDatabase {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            self::$pdo = Config::getConnexion();
        }

        return self::$pdo;
    }
}