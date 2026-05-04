<?php
// Model: Aliment (BackOffice — full CRUD)

class Aliment {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ===== CRUD =====

    public function ajouter($nom, $categorie, $kcal_portion, $co2_impact, $prix_unitaire, $est_bio, $est_local, $image_url = null) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO aliment (nom, categorie, kcal_portion, co2_impact, prix_unitaire, est_bio, est_local, image_url)
             VALUES (:nom, :categorie, :kcal_portion, :co2_impact, :prix_unitaire, :est_bio, :est_local, :image_url)"
        );
        $stmt->execute([
            ':nom' => $nom,
            ':categorie' => $categorie,
            ':kcal_portion' => $kcal_portion,
            ':co2_impact' => $co2_impact,
            ':prix_unitaire' => $prix_unitaire,
            ':est_bio' => $est_bio,
            ':est_local' => $est_local,
            ':image_url' => $image_url
        ]);
        return $this->pdo->lastInsertId();
    }

    public function listerTout() {
        $stmt = $this->pdo->query("SELECT * FROM aliment ORDER BY nom");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM aliment WHERE id_aliment = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function modifier($id, $nom, $categorie, $kcal_portion, $co2_impact, $prix_unitaire, $est_bio, $est_local, $image_url = null) {
        // Si $image_url est null, on conserve la valeur existante (COALESCE).
        $stmt = $this->pdo->prepare(
            "UPDATE aliment SET nom = :nom, categorie = :categorie, kcal_portion = :kcal_portion,
             co2_impact = :co2_impact, prix_unitaire = :prix_unitaire, est_bio = :est_bio, est_local = :est_local,
             image_url = COALESCE(:image_url, image_url)
             WHERE id_aliment = :id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':nom' => $nom,
            ':categorie' => $categorie,
            ':kcal_portion' => $kcal_portion,
            ':co2_impact' => $co2_impact,
            ':prix_unitaire' => $prix_unitaire,
            ':est_bio' => $est_bio,
            ':est_local' => $est_local,
            ':image_url' => $image_url
        ]);
    }

    public function supprimer($id) {
        $stmt = $this->pdo->prepare("DELETE FROM aliment WHERE id_aliment = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ===== Recherche / filtres =====

    /**
     * Recherche d'aliments avec filtres optionnels.
     * @param string|null $q          mot-clé sur le nom
     * @param string|null $categorie  catégorie exacte
     * @param string|null $bio        '1' / '0' / null
     * @param string|null $local      '1' / '0' / null
     */
    public function rechercher($q = null, $categorie = null, $bio = null, $local = null) {
        $sql = "SELECT * FROM aliment WHERE 1=1";
        $params = [];

        if ($q !== null && $q !== '') {
            $sql .= " AND nom LIKE :q";
            $params[':q'] = '%' . $q . '%';
        }
        if ($categorie !== null && $categorie !== '') {
            $sql .= " AND categorie = :categorie";
            $params[':categorie'] = $categorie;
        }
        if ($bio === '1' || $bio === '0') {
            $sql .= " AND est_bio = :bio";
            $params[':bio'] = (int)$bio;
        }
        if ($local === '1' || $local === '0') {
            $sql .= " AND est_local = :local";
            $params[':local'] = (int)$local;
        }

        $sql .= " ORDER BY nom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCategories() {
        $stmt = $this->pdo->query(
            "SELECT DISTINCT categorie FROM aliment ORDER BY categorie"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ===== Statistiques =====

    public function statistiques() {
        $stats = [
            'total' => 0, 'nb_bio' => 0, 'nb_local' => 0,
            'kcal_moyen' => 0, 'co2_moyen' => 0, 'par_categorie' => []
        ];

        $row = $this->pdo->query(
            "SELECT COUNT(*) AS total,
                    SUM(est_bio) AS nb_bio,
                    SUM(est_local) AS nb_local,
                    AVG(kcal_portion) AS kcal_moyen,
                    AVG(co2_impact) AS co2_moyen
             FROM aliment"
        )->fetch();

        if ($row) {
            $stats['total']      = (int)$row['total'];
            $stats['nb_bio']     = (int)$row['nb_bio'];
            $stats['nb_local']   = (int)$row['nb_local'];
            $stats['kcal_moyen'] = round((float)$row['kcal_moyen'], 1);
            $stats['co2_moyen']  = round((float)$row['co2_moyen'], 2);
        }

        $stmt = $this->pdo->query(
            "SELECT categorie, COUNT(*) AS nb FROM aliment GROUP BY categorie ORDER BY nb DESC"
        );
        $stats['par_categorie'] = $stmt->fetchAll();

        return $stats;
    }
}
