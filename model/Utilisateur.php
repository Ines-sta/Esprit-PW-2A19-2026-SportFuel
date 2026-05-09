<?php
class Utilisateur {
    private $id;
    private $nom;
    private $email;
    private $password;
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

    public function __construct($id = null, $nom = '', $email = '', $password = '', $age = 0, $poids = 0, $taille = 0, $sport = 'Aucun', $objectif = 'Non défini', $niveau = 'Débutant', $frequence = 1, $role = 'Sportif', $statut = 'Actif') {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->password = $password;
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
    public function setAge($age) { $this->age = $age; }
    public function setPoids($poids) { $this->poids = $poids; }
    public function setTaille($taille) { $this->taille = $taille; }
    public function setSport($sport) { $this->sport = $sport; }
    public function setObjectif($objectif) { $this->objectif = $objectif; }
    public function setNiveau($niveau) { $this->niveau = $niveau; }
    public function setFrequence($frequence) { $this->frequence = $frequence; }
    public function setRole($role) { $this->role = $role; }
    public function setStatut($statut) { $this->statut = $statut; }

    private static function socialUserTableExists(PDO $pdo) {
        static $checked = false;
        static $exists = false;

        if ($checked) {
            return $exists;
        }

        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'user'");
            $exists = ($stmt && $stmt->fetchColumn() !== false);
        } catch (PDOException $e) {
            $exists = false;
        }

        $checked = true;
        return $exists;
    }

    private static function mirrorToSocialUser(PDO $pdo, $utilisateurId) {
        if (!self::socialUserTableExists($pdo)) {
            return;
        }

        try {
            $stmtUtilisateur = $pdo->prepare(
                "SELECT id, nom, email, mot_de_passe, role
                 FROM utilisateurs
                 WHERE id = :id
                 LIMIT 1"
            );
            $stmtUtilisateur->execute([':id' => (int)$utilisateurId]);
            $utilisateur = $stmtUtilisateur->fetch(PDO::FETCH_ASSOC);

            if (!$utilisateur) {
                return;
            }

            $stmtExisting = $pdo->prepare(
                "SELECT id_user
                 FROM `user`
                 WHERE utilisateur_id = :utilisateur_id OR email = :email
                 ORDER BY id_user ASC
                 LIMIT 1"
            );
            $stmtExisting->execute([
                ':utilisateur_id' => (int)$utilisateur['id'],
                ':email' => (string)$utilisateur['email']
            ]);
            $existing = $stmtExisting->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $stmtUpdate = $pdo->prepare(
                    "UPDATE `user`
                     SET utilisateur_id = :utilisateur_id,
                         nom = :nom,
                         prenom = :prenom,
                         email = :email,
                         password = :password,
                         role = :role
                     WHERE id_user = :id_user"
                );
                $stmtUpdate->execute([
                    ':utilisateur_id' => (int)$utilisateur['id'],
                    ':nom' => (string)$utilisateur['nom'],
                    ':prenom' => '',
                    ':email' => (string)$utilisateur['email'],
                    ':password' => (string)$utilisateur['mot_de_passe'],
                    ':role' => (string)($utilisateur['role'] ?? 'Sportif'),
                    ':id_user' => (int)$existing['id_user']
                ]);
                return;
            }

            $stmtInsert = $pdo->prepare(
                "INSERT INTO `user` (id_user, utilisateur_id, nom, prenom, email, password, role)
                 VALUES (:id_user, :utilisateur_id, :nom, :prenom, :email, :password, :role)"
            );
            $stmtInsert->execute([
                ':id_user' => (int)$utilisateur['id'],
                ':utilisateur_id' => (int)$utilisateur['id'],
                ':nom' => (string)$utilisateur['nom'],
                ':prenom' => '',
                ':email' => (string)$utilisateur['email'],
                ':password' => (string)$utilisateur['mot_de_passe'],
                ':role' => (string)($utilisateur['role'] ?? 'Sportif')
            ]);
        } catch (PDOException $e) {
            // Keep main utilisateurs write path resilient even if social bridge sync fails.
        }
    }

    private static function detachFromSocialUser(PDO $pdo, $utilisateurId) {
        if (!self::socialUserTableExists($pdo)) {
            return;
        }

        try {
            $stmt = $pdo->prepare("UPDATE `user` SET utilisateur_id = NULL WHERE utilisateur_id = :utilisateur_id");
            $stmt->execute([':utilisateur_id' => (int)$utilisateurId]);
        } catch (PDOException $e) {
            // Ignore detach failures to preserve legacy delete behavior.
        }
    }

    public static function syncSocialBridge(PDO $pdo, $utilisateurId) {
        self::mirrorToSocialUser($pdo, $utilisateurId);
    }

    public function save(PDO $pdo) {
        $sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, age, poids, taille, role, statut, sport_pratique, objectif, niveau, seances_semaine) 
                VALUES (:nom, :email, :password, :age, :poids, :taille, :role, :statut, :sport, :objectif, :niveau, :seances_semaine)";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([
            ':nom' => $this->nom,
            ':email' => $this->email,
            ':password' => $this->password,
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
            self::mirrorToSocialUser($pdo, $this->id);
        }

        return $success;
    }

    public function update(PDO $pdo) {
        $sql = "UPDATE utilisateurs SET nom = :nom, age = :age, poids = :poids, taille = :taille, sport_pratique = :sport, objectif = :objectif, niveau = :niveau, seances_semaine = :frequence WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([
            ':nom' => $this->nom,
            ':age' => $this->age,
            ':poids' => $this->poids,
            ':taille' => $this->taille,
            ':sport' => $this->sport,
            ':objectif' => $this->objectif,
            ':niveau' => $this->niveau,
            ':frequence' => $this->frequence,
            ':id' => $this->id
        ]);

        if ($success) {
            self::mirrorToSocialUser($pdo, $this->id);
        }

        return $success;
    }

    public static function findByEmail(PDO $pdo, $email) {
        $sql = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Utilisateur(
                $row['id'], $row['nom'], $row['email'], $row['mot_de_passe'], 
                $row['age'], $row['poids'], $row['taille'], 
                $row['sport_pratique'] ?? '', $row['objectif'] ?? '', $row['niveau'] ?? '', $row['seances_semaine'] ?? 1,
                $row['role'] ?? 'Sportif', $row['statut'] ?? 'Actif'
            );
        }
        return null;
    }

    public static function getAll(PDO $pdo) {
        try {
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
                    $row['role'] ?? 'Sportif', $row['statut'] ?? 'Actif'
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
            self::detachFromSocialUser($pdo, $id);
            $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
