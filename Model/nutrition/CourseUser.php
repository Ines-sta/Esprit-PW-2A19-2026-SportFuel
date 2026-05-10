<?php
// FrontOffice Model: Course (lecture + toggle achat + génération optimisée MVP)

class Course {
    private $pdo;

    public static $unitesAutorisees = ['g', 'kg', 'ml', 'L', 'piece'];

    public function __construct($pdo) {
        $this->pdo = $pdo;
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

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM course WHERE id_course = :id");
        $stmt->execute([':id' => $id]);
        $course = $stmt->fetch();
        if ($course) {
            $course['articles'] = $this->getArticles($id);
        }
        return $course;
    }

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

    public function rechercher($q = null, $statut = null, $id_utilisateur = null) {
        $sql = "SELECT c.*, COUNT(ca.id_aliment) AS nb_articles,
                       SUM(ca.achete) AS nb_achetes
                FROM course c
                LEFT JOIN course_aliment ca ON c.id_course = ca.id_course
                WHERE 1=1";
        $params = [];

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

        $sql .= " GROUP BY c.id_course ORDER BY c.date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ===== Helpers unités / kcal (mêmes règles qu'en BackOffice) =====

    public static function toGrams($quantite, $unite) {
        switch ($unite) {
            case 'g':     return (float)$quantite;
            case 'kg':    return (float)$quantite * 1000;
            case 'ml':    return (float)$quantite;
            case 'L':     return (float)$quantite * 1000;
            case 'piece': return null;
            default:      return (float)$quantite;
        }
    }

    public static function kcalArticle($article) {
        $grams = self::toGrams($article['quantite'], $article['unite'] ?? 'g');
        if ($grams === null) return null;
        return ($grams / 100.0) * (float)$article['kcal_portion'];
    }

    public static function kcalTotal($articles) {
        $total = 0;
        foreach ($articles as $art) {
            $k = self::kcalArticle($art);
            if ($k !== null) $total += $k;
        }
        return $total;
    }

    // ===== MVP Optimiseur =====

    /**
     * Score composite simple: énergie + prix + préférences bio/local.
     */
    private function calculerScoreAliment($aliment, $maxKcal, $maxPrix, $preferBio, $preferLocal) {
        $kcalNorm = $maxKcal > 0 ? ((float)$aliment['kcal_portion'] / $maxKcal) : 0;
        $prixNorm = $maxPrix > 0 ? ((float)$aliment['prix_unitaire'] / $maxPrix) : 1;
        $prixScore = max(0, 1 - $prixNorm);

        $bonusBio = ($preferBio && (int)$aliment['est_bio'] === 1) ? 0.1 : 0;
        $bonusLocal = ($preferLocal && (int)$aliment['est_local'] === 1) ? 0.1 : 0;

        return (0.5 * $kcalNorm) + (0.3 * $prixScore) + $bonusBio + $bonusLocal;
    }

    /**
     * Génère un aperçu optimisé à budget contraint.
     * Chaque "unité" représente 100g pour rester cohérent avec kcal_portion.
     */
    public function genererApercuOptimise($kcalTarget, $budgetMax, $preferBio = false, $preferLocal = false) {
        $kcalTarget = max(100, (float)$kcalTarget);
        $budgetMax = max(0.1, (float)$budgetMax);
        $preferBio = (int)$preferBio === 1;
        $preferLocal = (int)$preferLocal === 1;

        $preview = [
            'items' => [],
            'warnings' => [],
            'totals' => [
                'kcal_target' => $kcalTarget,
                'budget_max' => $budgetMax,
                'kcal_total' => 0,
                'cout_total' => 0,
                'couverture' => 0,
                'restant' => $budgetMax,
            ],
        ];

        $stmt = $this->pdo->query(
            "SELECT id_aliment, nom, categorie, kcal_portion, prix_unitaire, est_bio, est_local
             FROM aliment
             WHERE prix_unitaire > 0
             ORDER BY nom"
        );
        $aliments = $stmt->fetchAll();

        if (empty($aliments)) {
            $preview['warnings'][] = "Aucun aliment avec prix unitaire disponible.";
            return $preview;
        }

        $maxKcal = 0;
        $maxPrix = 0;
        foreach ($aliments as $a) {
            $maxKcal = max($maxKcal, (float)$a['kcal_portion']);
            $maxPrix = max($maxPrix, (float)$a['prix_unitaire']);
        }

        foreach ($aliments as &$a) {
            $a['score'] = $this->calculerScoreAliment($a, $maxKcal, $maxPrix, $preferBio, $preferLocal);
            $a['kcal_per_cost'] = (float)$a['kcal_portion'] / max((float)$a['prix_unitaire'], 0.01);
        }
        unset($a);

        usort($aliments, function ($left, $right) {
            if ($right['score'] == $left['score']) {
                return $right['kcal_per_cost'] <=> $left['kcal_per_cost'];
            }
            return $right['score'] <=> $left['score'];
        });

        $selected = [];
        $totalKcal = 0.0;
        $totalCost = 0.0;

        // 1) Panier de base: on ajoute des blocs 100g sans dépasser la cible kcal.
        $maxInitialItems = 6;
        foreach ($aliments as $a) {
            if (count($selected) >= $maxInitialItems) break;

            $kcal100 = (float)$a['kcal_portion'];
            $prix100 = (float)$a['prix_unitaire'];
            if ($kcal100 <= 0 || $prix100 <= 0) continue;
            if (($totalCost + $prix100) > $budgetMax) continue;
            if (($totalKcal + $kcal100) > $kcalTarget) continue;

            $id = (int)$a['id_aliment'];
            $selected[$id] = [
                'id_aliment' => $id,
                'nom' => $a['nom'],
                'categorie' => $a['categorie'],
                'quantite' => 100,
                'unite' => 'g',
                'kcal' => $kcal100,
                'cout' => $prix100,
                'score' => (float)$a['score'],
                'est_bio' => (int)$a['est_bio'],
                'est_local' => (int)$a['est_local'],
            ];

            $totalKcal += $kcal100;
            $totalCost += $prix100;
        }

        // 2) Affinage: ajout incrémental de 50g en restant sous la cible et le budget.
        $incrementGrams = 50;
        $guard = 0;
        while ($guard < 400) {
            $remainingKcal = $kcalTarget - $totalKcal;
            $remainingBudget = $budgetMax - $totalCost;
            if ($remainingKcal < 5 || $remainingBudget < 0.01) break;

            $bestCandidate = null;
            foreach ($aliments as $a) {
                $incKcal = ((float)$a['kcal_portion']) * ($incrementGrams / 100.0);
                $incCost = ((float)$a['prix_unitaire']) * ($incrementGrams / 100.0);
                if ($incKcal <= 0 || $incCost <= 0) continue;
                if ($incKcal > $remainingKcal) continue;
                if ($incCost > $remainingBudget) continue;

                $candidateScore = (float)$a['score'] + (((float)$a['kcal_per_cost']) / 10000.0);
                if ($bestCandidate === null || $candidateScore > $bestCandidate['candidateScore']) {
                    $bestCandidate = [
                        'id_aliment' => (int)$a['id_aliment'],
                        'nom' => $a['nom'],
                        'categorie' => $a['categorie'],
                        'score' => (float)$a['score'],
                        'est_bio' => (int)$a['est_bio'],
                        'est_local' => (int)$a['est_local'],
                        'incKcal' => $incKcal,
                        'incCost' => $incCost,
                        'candidateScore' => $candidateScore,
                    ];
                }
            }

            if ($bestCandidate === null) break;

            $id = $bestCandidate['id_aliment'];
            if (!isset($selected[$id])) {
                $selected[$id] = [
                    'id_aliment' => $id,
                    'nom' => $bestCandidate['nom'],
                    'categorie' => $bestCandidate['categorie'],
                    'quantite' => 0,
                    'unite' => 'g',
                    'kcal' => 0,
                    'cout' => 0,
                    'score' => $bestCandidate['score'],
                    'est_bio' => $bestCandidate['est_bio'],
                    'est_local' => $bestCandidate['est_local'],
                ];
            }

            $selected[$id]['quantite'] += $incrementGrams;
            $selected[$id]['kcal'] += $bestCandidate['incKcal'];
            $selected[$id]['cout'] += $bestCandidate['incCost'];

            $totalKcal += $bestCandidate['incKcal'];
            $totalCost += $bestCandidate['incCost'];
            $guard++;
        }

        if (empty($selected)) {
            $preview['warnings'][] = "Budget trop faible pour proposer un panier.";
            return $preview;
        }

        // Tolérance MVP: on n'alerte que si le déficit est significatif.
        // Ex: 1990.5 / 2000 (0.5% de déficit) ne déclenche plus d'alerte rouge.
        if ($totalKcal < $kcalTarget) {
            $deficitKcal = $kcalTarget - $totalKcal;
            $deficitPercent = $kcalTarget > 0 ? ($deficitKcal / $kcalTarget) * 100 : 0;
            if ($deficitPercent > 5) {
                $preview['warnings'][] = "Objectif kcal non atteint avec le budget actuel.";
            }
        }

        foreach ($selected as &$item) {
            $item['kcal'] = round((float)$item['kcal'], 1);
            $item['cout'] = round((float)$item['cout'], 2);
            $item['score'] = round((float)$item['score'], 3);
        }
        unset($item);

        $preview['items'] = array_values($selected);
        $preview['totals'] = [
            'kcal_target' => round($kcalTarget, 1),
            'budget_max' => round($budgetMax, 2),
            'kcal_total' => round($totalKcal, 1),
            'cout_total' => round($totalCost, 2),
            'couverture' => $kcalTarget > 0 ? round(($totalKcal / $kcalTarget) * 100, 1) : 100,
            'restant' => round(max(0, $budgetMax - $totalCost), 2),
        ];

        usort($preview['items'], function ($left, $right) {
            return $right['score'] <=> $left['score'];
        });

        return $preview;
    }

    /**
     * Crée une liste de course à partir d'un aperçu optimisé confirmé par l'utilisateur.
     */
    public function creerCourseOptimisee($idUtilisateur, $nom, $items, $statut = 'Non démarrée') {
        if (!is_array($items) || empty($items)) return 0;

        try {
            $this->pdo->beginTransaction();

            $stmtCourse = $this->pdo->prepare(
                "INSERT INTO course (id_utilisateur, nom, date, statut)
                 VALUES (:id_utilisateur, :nom, :date, :statut)"
            );
            $stmtCourse->execute([
                ':id_utilisateur' => (int)$idUtilisateur,
                ':nom' => $nom,
                ':date' => date('Y-m-d'),
                ':statut' => $statut,
            ]);

            $idCourse = (int)$this->pdo->lastInsertId();

            $stmtArticle = $this->pdo->prepare(
                "INSERT INTO course_aliment (id_course, id_aliment, quantite, unite, achete)
                 VALUES (:id_course, :id_aliment, :quantite, :unite, 0)"
            );

            $inserted = 0;
            foreach ($items as $item) {
                $idAliment = (int)($item['id_aliment'] ?? 0);
                $quantite = (float)($item['quantite'] ?? 0);
                $unite = $item['unite'] ?? 'g';

                if ($idAliment <= 0 || $quantite <= 0) continue;
                if (!in_array($unite, self::$unitesAutorisees, true)) $unite = 'g';

                $stmtArticle->execute([
                    ':id_course' => $idCourse,
                    ':id_aliment' => $idAliment,
                    ':quantite' => $quantite,
                    ':unite' => $unite,
                ]);
                $inserted++;
            }

            if ($inserted === 0) {
                throw new RuntimeException('Aucun article inséré.');
            }

            $this->pdo->commit();
            return $idCourse;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return 0;
        }
    }

    public function statistiques($id_utilisateur = null) {
        $stats = [
            'total' => 0, 'par_statut' => [], 'articles_moyen' => 0,
            'pourcent_achetes' => 0, 'total_kcal_global' => 0,
        ];

        $whereCourse = "";
        $params = [];
        if ($id_utilisateur !== null && $id_utilisateur !== '' && (int)$id_utilisateur > 0) {
            $whereCourse = " WHERE c.id_utilisateur = :id_utilisateur";
            $params[':id_utilisateur'] = (int)$id_utilisateur;
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM course c" . $whereCourse);
        $stmt->execute($params);
        $row = $stmt->fetch();
        $stats['total'] = (int)($row['total'] ?? 0);

        $stmt = $this->pdo->prepare(
            "SELECT c.statut, COUNT(*) AS nb
             FROM course c"
             . $whereCourse .
            " GROUP BY c.statut"
        );
        $stmt->execute($params);
        $stats['par_statut'] = $stmt->fetchAll();

        $stmt = $this->pdo->prepare(
            "SELECT AVG(t.nb) AS articles_moyen FROM (
                SELECT COUNT(ca.id_aliment) AS nb
                FROM course c
                LEFT JOIN course_aliment ca ON c.id_course = ca.id_course"
                . $whereCourse .
                " GROUP BY c.id_course
             ) AS t"
        );
        $stmt->execute($params);
        $row = $stmt->fetch();
        $stats['articles_moyen'] = round((float)($row['articles_moyen'] ?? 0), 1);

        $stmt = $this->pdo->prepare(
            "SELECT SUM(ca.achete) AS achetes, COUNT(*) AS total
             FROM course_aliment ca
             INNER JOIN course c ON c.id_course = ca.id_course"
             . $whereCourse
        );
        $stmt->execute($params);
        $row = $stmt->fetch();
        $totalArticles = (int)($row['total'] ?? 0);
        $stats['pourcent_achetes'] = $totalArticles > 0
            ? round(((int)$row['achetes'] / $totalArticles) * 100, 1)
            : 0;

        $stmt = $this->pdo->prepare(
            "SELECT ca.quantite, ca.unite, a.kcal_portion
             FROM course_aliment ca
             INNER JOIN aliment a ON ca.id_aliment = a.id_aliment
             INNER JOIN course c ON c.id_course = ca.id_course"
             . $whereCourse
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
}
