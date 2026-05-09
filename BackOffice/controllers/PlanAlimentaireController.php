<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/PlanAlimentaire.php';

/**
 * Canonical BackOffice controller for plans alimentaires.
 */
class PlanAlimentaireController {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    public function listPlans() {
        $sql = "SELECT * FROM PlanAlimentaire ORDER BY date_debut DESC";
        $stmt = $this->pdo->query($sql);
        $plans = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $plans[] = new PlanAlimentaire(
                $row['id_plan'],
                $row['id_utilisateur'],
                $row['nom'],
                $row['type'],
                $row['kcal_cibles'],
                $row['semaine'],
                $row['date_debut'],
                $row['date_fin']
            );
        }
        return $plans;
    }

    public function getPlan($id) {
        $sql = "SELECT * FROM PlanAlimentaire WHERE id_plan = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new PlanAlimentaire(
                $row['id_plan'],
                $row['id_utilisateur'],
                $row['nom'],
                $row['type'],
                $row['kcal_cibles'],
                $row['semaine'],
                $row['date_debut'],
                $row['date_fin']
            );
        }
        return null;
    }

    public function addPlan($plan) {
        $sql = "INSERT INTO PlanAlimentaire (id_utilisateur, nom, type, kcal_cibles, semaine, date_debut, date_fin)
                VALUES (:id_utilisateur, :nom, :type, :kcal_cibles, :semaine, :date_debut, :date_fin)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id_utilisateur' => $plan->getIdUtilisateur(),
            'nom' => $plan->getNom(),
            'type' => $plan->getType(),
            'kcal_cibles' => $plan->getKcalCibles(),
            'semaine' => $plan->getSemaine(),
            'date_debut' => $plan->getDateDebut(),
            'date_fin' => $plan->getDateFin()
        ]);
        header('Location: index.php?page=back&action=listPlans');
        exit;
    }

    public function updatePlan($plan) {
        $sql = "UPDATE PlanAlimentaire SET id_utilisateur = :id_utilisateur, nom = :nom, type = :type,
                kcal_cibles = :kcal_cibles, semaine = :semaine, date_debut = :date_debut, date_fin = :date_fin
                WHERE id_plan = :id_plan";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id_plan' => $plan->getIdPlan(),
            'id_utilisateur' => $plan->getIdUtilisateur(),
            'nom' => $plan->getNom(),
            'type' => $plan->getType(),
            'kcal_cibles' => $plan->getKcalCibles(),
            'semaine' => $plan->getSemaine(),
            'date_debut' => $plan->getDateDebut(),
            'date_fin' => $plan->getDateFin()
        ]);
        header('Location: index.php?page=back&action=listPlans');
        exit;
    }

    public function deletePlan($id) {
        $sql = "DELETE FROM PlanAlimentaire WHERE id_plan = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        header('Location: index.php?page=back&action=listPlans');
        exit;
    }

    public function getPlanWithRepas($id) {
        $plan = $this->getPlan($id);
        $sql = "SELECT * FROM Repas WHERE id_plan = :id_plan ORDER BY
                FIELD(jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'),
                FIELD(type_repas, 'petit_dejeuner', 'dejeuner', 'diner', 'collation')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_plan' => $id]);
        $repas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $repas[] = $row;
        }
        return ['plan' => $plan, 'repas' => $repas];
    }
}
