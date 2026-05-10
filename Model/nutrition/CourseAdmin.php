<?php
// Model: Course (BackOffice — full CRUD + recherche + stats + unités/kcal)

class Course {
    private $pdo;

    /** Unités autorisées (app-level enum) */
    public static $unitesAutorisees = ['g', 'kg', 'ml', 'L', 'piece'];

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ===== CRUD Course =====

    public function genererListeCourses($id_utilisateur, $nom, $date, $statut, $articles = [], $image_url = null) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO course (id_utilisateur, nom, date, statut, image_url)
             VALUES (:id_utilisateur, :nom, :date, :statut, :image_url)"
        );
        $stmt->execute([
            ':id_utilisateur' => $id_utilisateur,
            ':nom' => $nom,
            ':date' => $date,
            ':statut' => $statut,
            ':image_url' => $image_url
        ]);
        $id_course = $this->pdo->lastInsertId();

        foreach ($articles as $article) {
            $unite = $article['unite'] ?? 'g';
            $this->ajouterArticle($id_course, $article['id_aliment'], $article['quantite'], $unite);
        }

        return $id_course;
    }

    public function consulterCourses() {
        $stmt = $this->pdo->query(
            "SELECT c.*, COUNT(ca.id_aliment) AS nb_articles,
                    SUM(ca.achete) AS nb_achetes
             FROM course c
             LEFT JOIN course_aliment ca ON c.id_course = ca.id_course
             GROUP BY c.id_course
             ORDER BY c.date DESC"
        );
        return $stmt->fetchAll();
    }

    public function listerTout() {
        return $this->consulterCourses();
    }

    public function getById($id, $allowedUserIds = null) {
        $sql = "SELECT * FROM course WHERE id_course = :id";
        $params = [':id' => $id];

        if (is_array($allowedUserIds)) {
            $allowedUserIds = array_values(array_unique(array_map('intval', $allowedUserIds)));
            if (empty($allowedUserIds)) {
                return null;
            }
            $inClause = $this->buildInClause('allowed_user', $allowedUserIds, $params);
            $sql .= " AND id_utilisateur IN ($inClause)";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $course = $stmt->fetch();

        if ($course) {
            $course['articles'] = $this->getArticles($id);
        }
        return $course;
    }

    public function modifier($id, $id_utilisateur, $nom, $date, $statut, $image_url = null) {
        // Si $image_url est null, on conserve la valeur existante (COALESCE).
        $stmt = $this->pdo->prepare(
            "UPDATE course
             SET id_utilisateur = :id_utilisateur, nom = :nom, date = :date, statut = :statut,
                 image_url = COALESCE(:image_url, image_url)
             WHERE id_course = :id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':id_utilisateur' => $id_utilisateur,
            ':nom' => $nom,
            ':date' => $date,
            ':statut' => $statut,
            ':image_url' => $image_url
        ]);
    }

    public function supprimer($id) {
        $stmt = $this->pdo->prepare("DELETE FROM course WHERE id_course = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ===== Articles (course_aliment) =====

    public function getArticles($id_course) {
        $stmt = $this->pdo->prepare(
            "SELECT ca.*, a.nom, a.categorie, a.kcal_portion, a.co2_impact, a.est_bio, a.est_local
             FROM course_aliment ca
             INNER JOIN aliment a ON ca.id_aliment = a.id_aliment
             WHERE ca.id_course = :id_course
             ORDER BY a.categorie, a.nom"
        );
        $stmt->execute([':id_course' => $id_course]);
        return $stmt->fetchAll();
    }

    public function ajouterArticle($id_course, $id_aliment, $quantite, $unite = 'g') {
        if (!in_array($unite, self::$unitesAutorisees, true)) {
            $unite = 'g';
        }
        $stmt = $this->pdo->prepare(
            "INSERT INTO course_aliment (id_course, id_aliment, quantite, unite, achete)
             VALUES (:id_course, :id_aliment, :quantite, :unite, 0)"
        );
        return $stmt->execute([
            ':id_course' => $id_course,
            ':id_aliment' => $id_aliment,
            ':quantite' => $quantite,
            ':unite' => $unite
        ]);
    }

    public function supprimerArticle($id_course, $id_aliment) {
        $stmt = $this->pdo->prepare(
            "DELETE FROM course_aliment WHERE id_course = :id_course AND id_aliment = :id_aliment"
        );
        return $stmt->execute([
            ':id_course' => $id_course,
            ':id_aliment' => $id_aliment
        ]);
    }

    public function marquerAchete($id_course, $id_aliment) {
        $stmt = $this->pdo->prepare(
            "UPDATE course_aliment SET achete = NOT achete
             WHERE id_course = :id_course AND id_aliment = :id_aliment"
        );
        return $stmt->execute([
            ':id_course' => $id_course,
            ':id_aliment' => $id_aliment
        ]);
    }

    // ===== Helpers unités / kcal =====

    /**
     * Convertit (quantite, unite) en grammes, ou null si non convertible (piece).
     * ml ≈ 1 g/ml (approximation documentée).
     */
    public static function toGrams($quantite, $unite) {
        switch ($unite) {
            case 'g':     return (float)$quantite;
            case 'kg':    return (float)$quantite * 1000;
            case 'ml':    return (float)$quantite;          // 1 g/ml
            case 'L':     return (float)$quantite * 1000;
            case 'piece': return null;
            default:      return (float)$quantite;
        }
    }

    /**
     * Calcule les kcal d'un article. Retourne null si non calculable (unité 'piece').
     */
    public static function kcalArticle($article) {
        $grams = self::toGrams($article['quantite'], $article['unite'] ?? 'g');
        if ($grams === null) return null;
        return ($grams / 100.0) * (float)$article['kcal_portion'];
    }

    /**
     * Total kcal d'une liste d'articles (les 'piece' sont ignorés).
     */
    public static function kcalTotal($articles) {
        $total = 0;
        foreach ($articles as $art) {
            $k = self::kcalArticle($art);
            if ($k !== null) $total += $k;
        }
        return $total;
    }

    // ===== Recherche / filtres =====

    public function rechercher($q = null, $statut = null, $id_utilisateur = null, $date_min = null, $date_max = null, $allowedUserIds = null) {
        $sql = "SELECT c.*, COUNT(ca.id_aliment) AS nb_articles,
                       SUM(ca.achete) AS nb_achetes
                FROM course c
                LEFT JOIN course_aliment ca ON c.id_course = ca.id_course
                WHERE 1=1";
        $params = [];

        if (is_array($allowedUserIds)) {
            $allowedUserIds = array_values(array_unique(array_map('intval', $allowedUserIds)));
            if (empty($allowedUserIds)) {
                return [];
            }
            $inClause = $this->buildInClause('allowed_user', $allowedUserIds, $params);
            $sql .= " AND c.id_utilisateur IN ($inClause)";
        }

        if ($q !== null && $q !== '') {
            $sql .= " AND c.nom LIKE :q";
            $params[':q'] = '%' . $q . '%';
        }
        if ($statut !== null && $statut !== '') {
            $sql .= " AND c.statut = :statut";
            $params[':statut'] = $statut;
        }
        if ($id_utilisateur !== null && $id_utilisateur !== '' && (int)$id_utilisateur > 0) {
            $sql .= " AND c.id_utilisateur = :id_utilisateur";
            $params[':id_utilisateur'] = (int)$id_utilisateur;
        }
        if ($date_min !== null && $date_min !== '') {
            $sql .= " AND c.date >= :date_min";
            $params[':date_min'] = $date_min;
        }
        if ($date_max !== null && $date_max !== '') {
            $sql .= " AND c.date <= :date_max";
            $params[':date_max'] = $date_max;
        }

        $sql .= " GROUP BY c.id_course ORDER BY c.date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ===== Statistiques =====

    public function statistiques($allowedUserIds = null) {
        $stats = [
            'total' => 0,
            'par_statut' => [],
            'articles_moyen' => 0,
            'pourcent_achetes' => 0,
            'total_kcal_global' => 0,
        ];

        $params = [];
        $where = '';
        if (is_array($allowedUserIds)) {
            $allowedUserIds = array_values(array_unique(array_map('intval', $allowedUserIds)));
            if (empty($allowedUserIds)) {
                return $stats;
            }
            $inClause = $this->buildInClause('allowed_user', $allowedUserIds, $params);
            $where = " WHERE c.id_utilisateur IN ($inClause)";
        }

        $stmtTotal = $this->pdo->prepare("SELECT COUNT(*) AS total FROM course c" . $where);
        $stmtTotal->execute($params);
        $row = $stmtTotal->fetch();
        $stats['total'] = (int)($row['total'] ?? 0);

        $stmt = $this->pdo->prepare(
            "SELECT c.statut, COUNT(*) AS nb FROM course c" . $where . " GROUP BY c.statut"
        );
        $stmt->execute($params);
        $stats['par_statut'] = $stmt->fetchAll();

        $stmtAverage = $this->pdo->prepare(
            "SELECT AVG(nb) AS articles_moyen FROM (
                SELECT COUNT(ca.id_aliment) AS nb
                FROM course c
                LEFT JOIN course_aliment ca ON ca.id_course = c.id_course" .
                $where .
                " GROUP BY c.id_course
             ) AS t"
        );
        $stmtAverage->execute($params);
        $row = $stmtAverage->fetch();
        $stats['articles_moyen'] = round((float)($row['articles_moyen'] ?? 0), 1);

        $stmtProgress = $this->pdo->prepare(
            "SELECT SUM(ca.achete) AS achetes, COUNT(*) AS total
             FROM course_aliment ca
             INNER JOIN course c ON c.id_course = ca.id_course" . $where
        );
        $stmtProgress->execute($params);
        $row = $stmtProgress->fetch();
        $totalArticles = (int)($row['total'] ?? 0);
        $stats['pourcent_achetes'] = $totalArticles > 0
            ? round(((int)$row['achetes'] / $totalArticles) * 100, 1)
            : 0;

        // Total kcal global (unités honorées, 'piece' exclu)
        $stmt = $this->pdo->prepare(
            "SELECT ca.quantite, ca.unite, a.kcal_portion
             FROM course_aliment ca
             INNER JOIN course c ON c.id_course = ca.id_course
             INNER JOIN aliment a ON ca.id_aliment = a.id_aliment" . $where
        );
        $stmt->execute($params);
        $totalKcal = 0;
        foreach ($stmt->fetchAll() as $art) {
            $k = self::kcalArticle($art);
            if ($k !== null) $totalKcal += $k;
        }
        $stats['total_kcal_global'] = round($totalKcal, 0);

        return $stats;
    }

    private function buildInClause($prefix, $values, &$params) {
        $placeholders = [];
        foreach ($values as $index => $value) {
            $key = ':' . $prefix . '_' . $index;
            $placeholders[] = $key;
            $params[$key] = (int)$value;
        }
        return implode(',', $placeholders);
    }
}
