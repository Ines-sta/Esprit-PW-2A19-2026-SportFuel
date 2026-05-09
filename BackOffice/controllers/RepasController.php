<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/Repas.php';

/**
 * Canonical BackOffice controller for repas.
 */
class RepasController {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    public function listRepas() {
        $sql = "SELECT r.*, p.nom as plan_nom FROM Repas r
                JOIN PlanAlimentaire p ON r.id_plan = p.id_plan
                ORDER BY r.id_repas DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listRepasByPlan($id_plan) {
        $sql = "SELECT r.*, p.nom as plan_nom FROM Repas r
                JOIN PlanAlimentaire p ON r.id_plan = p.id_plan
                WHERE r.id_plan = :id_plan";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_plan' => $id_plan]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRepas($id) {
        $sql = "SELECT * FROM Repas WHERE id_repas = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Repas(
                $row['id_repas'],
                $row['id_plan'],
                $row['jour'],
                $row['type_repas'],
                $row['description'],
                $row['kcal']
            );
        }
        return null;
    }

    public function addRepas($repas) {
        $sql = "INSERT INTO Repas (id_plan, jour, type_repas, description, kcal)
                VALUES (:id_plan, :jour, :type_repas, :description, :kcal)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id_plan' => $repas->getIdPlan(),
            'jour' => $repas->getJour(),
            'type_repas' => $repas->getTypeRepas(),
            'description' => $repas->getDescription(),
            'kcal' => $repas->getKcal()
        ]);
        header('Location: index.php?page=back&action=listRepas');
        exit;
    }

    public function updateRepas($repas) {
        $sql = "UPDATE Repas SET id_plan = :id_plan, jour = :jour, type_repas = :type_repas,
                description = :description, kcal = :kcal WHERE id_repas = :id_repas";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id_repas' => $repas->getIdRepas(),
            'id_plan' => $repas->getIdPlan(),
            'jour' => $repas->getJour(),
            'type_repas' => $repas->getTypeRepas(),
            'description' => $repas->getDescription(),
            'kcal' => $repas->getKcal()
        ]);
        header('Location: index.php?page=back&action=listRepas');
        exit;
    }

    public function deleteRepas($id) {
        $sql = "DELETE FROM Repas WHERE id_repas = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        header('Location: index.php?page=back&action=listRepas');
        exit;
    }
}
