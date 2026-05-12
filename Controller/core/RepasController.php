<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/nutrition/Repas.php';
require_once __DIR__ . '/PlanAlimentaireController.php';

/**
 * Canonical BackOffice controller for repas.
 */
class RepasController {
    private $pdo;
    private $planController;
    private $hasRepasAlimentTable;

    public function __construct() {
        $this->pdo = Config::getConnexion();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->planController = new PlanAlimentaireController();
        $this->hasRepasAlimentTable = null;
    }

    public function listRepas() {
        if ($this->getCurrentRole() === 'Coach') {
            $coachId = $this->resolveSessionUtilisateurId();
            if ($this->hasRepasAlimentTable()) {
                $sql = "SELECT r.*, p.nom as plan_nom, COUNT(ra.id_aliment) AS aliments_count
                        FROM Repas r
                        JOIN PlanAlimentaire p ON r.id_plan = p.id_plan
                        JOIN coach_sportif_assignments a ON a.id_sportif = p.id_utilisateur
                        LEFT JOIN repas_aliment ra ON ra.id_repas = r.id_repas
                        WHERE a.id_coach = :id_coach
                        GROUP BY r.id_repas
                        ORDER BY r.id_repas DESC";
            } else {
                $sql = "SELECT r.*, p.nom as plan_nom
                        FROM Repas r
                        JOIN PlanAlimentaire p ON r.id_plan = p.id_plan
                        JOIN coach_sportif_assignments a ON a.id_sportif = p.id_utilisateur
                        WHERE a.id_coach = :id_coach
                        ORDER BY r.id_repas DESC";
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_coach' => $coachId]);
        } else {
            if ($this->hasRepasAlimentTable()) {
                $sql = "SELECT r.*, p.nom as plan_nom, COUNT(ra.id_aliment) AS aliments_count
                        FROM Repas r
                        JOIN PlanAlimentaire p ON r.id_plan = p.id_plan
                        LEFT JOIN repas_aliment ra ON ra.id_repas = r.id_repas
                        GROUP BY r.id_repas
                        ORDER BY r.id_repas DESC";
                $stmt = $this->pdo->query($sql);
            } else {
                $sql = "SELECT r.*, p.nom as plan_nom FROM Repas r
                        JOIN PlanAlimentaire p ON r.id_plan = p.id_plan
                        ORDER BY r.id_repas DESC";
                $stmt = $this->pdo->query($sql);
            }
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listRepasByPlan($id_plan) {
        if (!$this->isPlanAllowedForCurrentRole((int)$id_plan)) {
            return [];
        }

        if ($this->hasRepasAlimentTable()) {
            $sql = "SELECT r.*, p.nom as plan_nom, COUNT(ra.id_aliment) AS aliments_count
                FROM Repas r
                JOIN PlanAlimentaire p ON r.id_plan = p.id_plan
                LEFT JOIN repas_aliment ra ON ra.id_repas = r.id_repas
                WHERE r.id_plan = :id_plan
                GROUP BY r.id_repas";
        } else {
            $sql = "SELECT r.*, p.nom as plan_nom FROM Repas r
                JOIN PlanAlimentaire p ON r.id_plan = p.id_plan
                WHERE r.id_plan = :id_plan";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_plan' => $id_plan]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRepas($id) {
        if ($this->getCurrentRole() === 'Coach') {
            $coachId = $this->resolveSessionUtilisateurId();
            $sql = "SELECT r.*
                    FROM Repas r
                    JOIN PlanAlimentaire p ON r.id_plan = p.id_plan
                    JOIN coach_sportif_assignments a ON a.id_sportif = p.id_utilisateur
                    WHERE r.id_repas = :id
                      AND a.id_coach = :id_coach";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'id_coach' => $coachId,
            ]);
        } else {
            $sql = "SELECT * FROM Repas WHERE id_repas = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Repas(
                $row['id_repas'],
                $row['id_plan'],
                $row['jour'],
                $row['type_repas'],
                $row['description'],
                $row['kcal'],
                $row['ingredients'] ?? null
            );
        }
        return null;
    }

    public function addRepas($repas) {
        if (!$this->isPlanAllowedForCurrentRole((int)$repas->getIdPlan())) {
            throw new RuntimeException('Plan non autorise pour ce role.');
        }

        $items = $this->extractRepasItemsFromRequest();
        $description = (string)$repas->getDescription();
        $ingredients = $repas->getIngredients(); // Récupérer les ingrédients
        $kcal = (int)$repas->getKcal();
        if (!empty($items)) {
            $computed = $this->computeRepasSummaryFromItems($items);
            if ($computed['description'] !== '') {
                $description = $computed['description'];
            }
            $kcal = $computed['kcal'];
        }

        $sql = "INSERT INTO Repas (id_plan, jour, type_repas, description, ingredients, kcal)
                VALUES (:id_plan, :jour, :type_repas, :description, :ingredients, :kcal)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id_plan' => $repas->getIdPlan(),
            'jour' => $repas->getJour(),
            'type_repas' => $repas->getTypeRepas(),
            'description' => $description,
            'ingredients' => $ingredients,
            'kcal' => $kcal
        ]);

        $newRepasId = (int)$this->pdo->lastInsertId();
        if ($newRepasId > 0 && $this->hasRepasAlimentTable()) {
            $this->saveRepasItems($newRepasId, $items);
        }

        header('Location: index.php?page=back&action=listRepas');
        exit;
    }

    public function updateRepas($repas) {
        if (!$this->isPlanAllowedForCurrentRole((int)$repas->getIdPlan())) {
            throw new RuntimeException('Plan non autorise pour ce role.');
        }

        $items = $this->extractRepasItemsFromRequest();
        $description = (string)$repas->getDescription();
        $ingredients = $repas->getIngredients(); // Récupérer les ingrédients
        $kcal = (int)$repas->getKcal();
        if (!empty($items)) {
            $computed = $this->computeRepasSummaryFromItems($items);
            if ($computed['description'] !== '') {
                $description = $computed['description'];
            }
            $kcal = $computed['kcal'];
        }

        $sql = "UPDATE Repas SET id_plan = :id_plan, jour = :jour, type_repas = :type_repas,
                description = :description, ingredients = :ingredients, kcal = :kcal WHERE id_repas = :id_repas";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id_repas' => $repas->getIdRepas(),
            'id_plan' => $repas->getIdPlan(),
            'jour' => $repas->getJour(),
            'type_repas' => $repas->getTypeRepas(),
            'description' => $description,
            'ingredients' => $ingredients,
            'kcal' => $kcal
        ]);

        if ($this->hasRepasAlimentTable()) {
            $this->saveRepasItems((int)$repas->getIdRepas(), $items);
        }

        header('Location: index.php?page=back&action=listRepas');
        exit;
    }

    public function getSelectableAliments() {
        $sql = "SELECT id_aliment, nom, kcal_portion FROM aliment ORDER BY nom ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getRepasAliments($idRepas) {
        if (!$this->hasRepasAlimentTable()) {
            return [];
        }

        $sql = "SELECT ra.id_aliment, a.nom, a.kcal_portion, ra.quantite, ra.unite
                FROM repas_aliment ra
                JOIN aliment a ON a.id_aliment = ra.id_aliment
                WHERE ra.id_repas = :id_repas
                ORDER BY a.nom ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_repas' => (int)$idRepas]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteRepas($id) {
        if ($this->getCurrentRole() === 'Coach') {
            $repas = $this->getRepas((int)$id);
            if (!$repas) {
                throw new RuntimeException('Repas introuvable ou non autorise.');
            }
        }

        $sql = "DELETE FROM Repas WHERE id_repas = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        header('Location: index.php?page=back&action=listRepas');
        exit;
    }

    public function isPlanAllowedForCurrentRole($idPlan) {
        if ($idPlan <= 0) {
            return false;
        }
        return $this->planController->getPlan((int)$idPlan) !== null;
    }

    private function hasRepasAlimentTable() {
        if ($this->hasRepasAlimentTable !== null) {
            return $this->hasRepasAlimentTable;
        }

        $stmt = $this->pdo->query("SHOW TABLES LIKE 'repas_aliment'");
        $this->hasRepasAlimentTable = (bool)($stmt && $stmt->fetchColumn());
        return $this->hasRepasAlimentTable;
    }

    private function extractRepasItemsFromRequest() {
        $ids = $_POST['aliments'] ?? [];
        $quantites = $_POST['quantites'] ?? [];
        $unites = $_POST['unites'] ?? [];

        if (!is_array($ids) || !is_array($quantites) || !is_array($unites)) {
            return [];
        }

        $allowedUnits = ['g', 'kg', 'ml', 'L', 'piece'];
        $normalized = [];

        $count = max(count($ids), count($quantites), count($unites));
        for ($i = 0; $i < $count; $i++) {
            $idAliment = isset($ids[$i]) ? (int)$ids[$i] : 0;
            $quantite = isset($quantites[$i]) ? (float)$quantites[$i] : 0.0;
            $unite = isset($unites[$i]) ? trim((string)$unites[$i]) : 'g';

            if ($idAliment <= 0 || $quantite <= 0) {
                continue;
            }
            if (!in_array($unite, $allowedUnits, true)) {
                $unite = 'g';
            }

            $key = $idAliment . '|' . $unite;
            if (!isset($normalized[$key])) {
                $normalized[$key] = [
                    'id_aliment' => $idAliment,
                    'quantite' => 0.0,
                    'unite' => $unite,
                ];
            }
            $normalized[$key]['quantite'] += $quantite;
        }

        return array_values($normalized);
    }

    private function computeRepasSummaryFromItems($items) {
        if (empty($items)) {
            return ['description' => '', 'kcal' => 0];
        }

        $ids = array_values(array_unique(array_map(static function ($item) {
            return (int)$item['id_aliment'];
        }, $items)));

        if (empty($ids)) {
            return ['description' => '', 'kcal' => 0];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT id_aliment, nom, kcal_portion FROM aliment WHERE id_aliment IN ($placeholders)");
        $stmt->execute($ids);
        $aliments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byId = [];
        foreach ($aliments as $aliment) {
            $byId[(int)$aliment['id_aliment']] = $aliment;
        }

        $parts = [];
        $totalKcal = 0.0;

        foreach ($items as $item) {
            $idAliment = (int)$item['id_aliment'];
            $quantite = (float)$item['quantite'];
            $unite = (string)$item['unite'];

            if (!isset($byId[$idAliment])) {
                continue;
            }

            $aliment = $byId[$idAliment];
            $kcalPortion = (float)$aliment['kcal_portion'];
            $convertedPortion = 0.0;

            if ($unite === 'g' || $unite === 'ml') {
                $convertedPortion = $quantite / 100.0;
            } elseif ($unite === 'kg' || $unite === 'L') {
                $convertedPortion = $quantite * 10.0;
            } elseif ($unite === 'piece') {
                $convertedPortion = $quantite;
            }

            $totalKcal += ($convertedPortion * $kcalPortion);
            $parts[] = $aliment['nom'] . ' (' . rtrim(rtrim(number_format($quantite, 2, '.', ''), '0'), '.') . ' ' . $unite . ')';
        }

        return [
            'description' => implode(', ', $parts),
            'kcal' => (int)round($totalKcal),
        ];
    }

    private function saveRepasItems($idRepas, $items) {
        if (!$this->hasRepasAlimentTable()) {
            return;
        }

        $deleteStmt = $this->pdo->prepare("DELETE FROM repas_aliment WHERE id_repas = :id_repas");
        $deleteStmt->execute(['id_repas' => (int)$idRepas]);

        if (empty($items)) {
            return;
        }

        $insertStmt = $this->pdo->prepare(
            "INSERT INTO repas_aliment (id_repas, id_aliment, quantite, unite)
             VALUES (:id_repas, :id_aliment, :quantite, :unite)"
        );

        foreach ($items as $item) {
            $insertStmt->execute([
                'id_repas' => (int)$idRepas,
                'id_aliment' => (int)$item['id_aliment'],
                'quantite' => (float)$item['quantite'],
                'unite' => (string)$item['unite'],
            ]);
        }
    }

    private function getCurrentRole() {
        $sessionRole = trim((string)($_SESSION['role'] ?? 'Admin'));
        if (strcasecmp($sessionRole, 'Coach') === 0) {
            return 'Coach';
        }
        return 'Admin';
    }

    private function resolveSessionUtilisateurId() {
        $email = trim((string)($_SESSION['user_email'] ?? ''));
        if ($email === '') {
            return 0;
        }

        $stmt = $this->pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : 0;
    }
}
