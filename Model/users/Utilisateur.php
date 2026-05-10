<?php
class Utilisateur {
    private static $photoColumnChecked = false;
    private $id;
    private $nom;
    private $email;
    private $password;
    private $photo_profil_url;
    private $age;
    private $poids;
    private $taille;
    private $sport;
    private $objectif;
    private $niveau;
    private $frequence;
    private $role;
    private $statut;
    /** @var string|null rempli par getAll() / hydratation admin */
    public $date_inscription;

    public function __construct($id = null, $nom = '', $email = '', $password = '', $age = 0, $poids = 0, $taille = 0, $sport = 'Aucun', $objectif = 'Non défini', $niveau = 'Débutant', $frequence = 1, $role = 'Sportif', $statut = 'Actif', $photoProfilUrl = null) {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->password = $password;
        $this->photo_profil_url = $photoProfilUrl;
        $this->age = $age;
        $this->poids = $poids;
        $this->taille = $taille;
        $this->sport = $sport;
        $this->objectif = $objectif;
        $this->niveau = $niveau;
        $this->frequence = $frequence;
        $this->role = $role;
        $this->statut = $statut;
    }

    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getPhotoProfilUrl() { return $this->photo_profil_url; }
    public function getAge() { return $this->age; }
    public function getPoids() { return $this->poids; }
    public function getTaille() { return $this->taille; }
    public function getSport() { return $this->sport; }
    public function getObjectif() { return $this->objectif; }
    public function getNiveau() { return $this->niveau; }
    public function getFrequence() { return $this->frequence; }
    public function getRole() { return $this->role; }
    public function getStatut() { return $this->statut; }

    public function setNom($nom) { $this->nom = $nom; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = password_hash($password, PASSWORD_BCRYPT); }
    public function setPhotoProfilUrl($url) { $this->photo_profil_url = $url; }
    public function setAge($age) { $this->age = $age; }
    public function setPoids($poids) { $this->poids = $poids; }
    public function setTaille($taille) { $this->taille = $taille; }
    public function setSport($sport) { $this->sport = $sport; }
    public function setObjectif($objectif) { $this->objectif = $objectif; }
    public function setNiveau($niveau) { $this->niveau = $niveau; }
    public function setFrequence($frequence) { $this->frequence = $frequence; }
    public function setRole($role) { $this->role = $role; }
    public function setStatut($statut) { $this->statut = $statut; }

    private static function ensurePhotoColumn(PDO $pdo) {
        if (self::$photoColumnChecked) {
            return;
        }

        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM utilisateurs LIKE 'photo_profil_url'");
            $exists = $stmt && $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$exists) {
                $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN photo_profil_url VARCHAR(500) NULL AFTER mot_de_passe");
            }
        } catch (PDOException $e) {
            // Ignore here to avoid hard-failing reads if permissions are limited.
        }

        self::$photoColumnChecked = true;
    }

    public function save(PDO $pdo) {
        self::ensurePhotoColumn($pdo);
        $sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, photo_profil_url, age, poids, taille, role, statut, sport_pratique, objectif, niveau, seances_semaine) 
            VALUES (:nom, :email, :password, :photo_profil_url, :age, :poids, :taille, :role, :statut, :sport, :objectif, :niveau, :seances_semaine)";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([
            ':nom' => $this->nom,
            ':email' => $this->email,
            ':password' => $this->password,
            ':photo_profil_url' => $this->photo_profil_url,
            ':age' => $this->age,
            ':poids' => $this->poids,
            ':taille' => $this->taille,
            ':role' => $this->role,
            ':statut' => $this->statut,
            ':sport' => $this->sport,
            ':objectif' => $this->objectif,
            ':niveau' => $this->niveau,
            ':seances_semaine' => $this->frequence
        ]);

        if ($success) {
            $this->id = (int)$pdo->lastInsertId();
        }

        return $success;
    }

    public function update(PDO $pdo) {
        self::ensurePhotoColumn($pdo);
        $sql = "UPDATE utilisateurs SET nom = :nom, photo_profil_url = :photo_profil_url, age = :age, poids = :poids, taille = :taille, sport_pratique = :sport, objectif = :objectif, niveau = :niveau, seances_semaine = :frequence WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([
            ':nom' => $this->nom,
            ':photo_profil_url' => $this->photo_profil_url,
            ':age' => $this->age,
            ':poids' => $this->poids,
            ':taille' => $this->taille,
            ':sport' => $this->sport,
            ':objectif' => $this->objectif,
            ':niveau' => $this->niveau,
            ':frequence' => $this->frequence,
            ':id' => $this->id
        ]);

        return $success;
    }

    public static function findByEmail(PDO $pdo, $email) {
        self::ensurePhotoColumn($pdo);
        $sql = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Utilisateur(
                $row['id'], $row['nom'], $row['email'], $row['mot_de_passe'], 
                $row['age'], $row['poids'], $row['taille'], 
                $row['sport_pratique'] ?? '', $row['objectif'] ?? '', $row['niveau'] ?? '', $row['seances_semaine'] ?? 1,
                $row['role'] ?? 'Sportif', $row['statut'] ?? 'Actif', $row['photo_profil_url'] ?? null
            );
        }
        return null;
    }

    public static function getAll(PDO $pdo) {
        try {
            self::ensurePhotoColumn($pdo);
            /* id toujours présent ; date_inscription peut manquer sur d'anciennes BDD */
            $sql = "SELECT * FROM utilisateurs ORDER BY id DESC";
            $stmt = $pdo->query($sql);
            if (!$stmt) return [];
            
            $users = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user = new Utilisateur(
                    $row['id'], $row['nom'], $row['email'], $row['mot_de_passe'], 
                    $row['age'], $row['poids'], $row['taille'], 
                    $row['sport_pratique'] ?? '', $row['objectif'] ?? '', $row['niveau'] ?? '', $row['seances_semaine'] ?? 1,
                    $row['role'] ?? 'Sportif', $row['statut'] ?? 'Actif', $row['photo_profil_url'] ?? null
                );
                $user->date_inscription = $row['date_inscription'] ?? '';
                $users[] = $user;
            }
            return $users;
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function getStats(PDO $pdo) {
        try {
            return [
                'total' => $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn(),
                'sportifs' => $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'Sportif'")->fetchColumn(),
                'coachs' => $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'Coach'")->fetchColumn(),
                'inactifs' => $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE statut = 'Inactif'")->fetchColumn(),
            ];
        } catch (PDOException $e) {
            return ['total' => 0, 'sportifs' => 0, 'coachs' => 0, 'inactifs' => 0];
        }
    }

    public static function delete(PDO $pdo, $id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
