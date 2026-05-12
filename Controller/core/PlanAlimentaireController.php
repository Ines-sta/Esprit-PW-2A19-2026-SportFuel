<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/nutrition/PlanAlimentaire.php';

/**
 * Canonical BackOffice controller for plans alimentaires.
 */
class PlanAlimentaireController {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function listPlans() {
        $role = $this->getCurrentRole();
        if ($role === 'Coach') {
            $coachId = $this->resolveSessionUtilisateurId();
            if ($coachId <= 0) {
                return [];
            }
            $sql = "SELECT p.*
                    FROM PlanAlimentaire p
                    INNER JOIN coach_sportif_assignments a
                        ON a.id_sportif = p.id_utilisateur
                    WHERE a.id_coach = :id_coach
                    ORDER BY p.date_debut DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_coach' => $coachId]);
        } elseif ($role === 'Sportif') {
            $sportifId = $this->resolveSessionUtilisateurId();
            if ($sportifId <= 0) {
                return [];
            }
            $sql = "SELECT *
                    FROM PlanAlimentaire
                    WHERE id_utilisateur = :id_utilisateur
                    ORDER BY date_debut DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_utilisateur' => $sportifId]);
        } else {
            $sql = "SELECT * FROM PlanAlimentaire ORDER BY date_debut DESC";
            $stmt = $this->pdo->query($sql);
        }
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
        $role = $this->getCurrentRole();
        if ($role === 'Coach') {
            $coachId = $this->resolveSessionUtilisateurId();
            if ($coachId <= 0) {
                return null;
            }
            $sql = "SELECT p.*
                    FROM PlanAlimentaire p
                    INNER JOIN coach_sportif_assignments a
                        ON a.id_sportif = p.id_utilisateur
                    WHERE p.id_plan = :id
                      AND a.id_coach = :id_coach";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'id_coach' => $coachId,
            ]);
        } elseif ($role === 'Sportif') {
            $sportifId = $this->resolveSessionUtilisateurId();
            if ($sportifId <= 0) {
                return null;
            }
            $sql = "SELECT *
                    FROM PlanAlimentaire
                    WHERE id_plan = :id
                      AND id_utilisateur = :id_utilisateur";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'id_utilisateur' => $sportifId,
            ]);
        } else {
            $sql = "SELECT * FROM PlanAlimentaire WHERE id_plan = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
        }
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
        if (!$this->isUtilisateurAllowedForCurrentRole((int)$plan->getIdUtilisateur())) {
            throw new RuntimeException('Utilisateur non autorise pour ce role.');
        }

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
        if (!$this->isUtilisateurAllowedForCurrentRole((int)$plan->getIdUtilisateur())) {
            throw new RuntimeException('Utilisateur non autorise pour ce role.');
        }

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
        $role = $this->getCurrentRole();
        if ($role === 'Sportif') {
            throw new RuntimeException('Suppression non autorisee pour ce role.');
        }

        if ($role === 'Coach') {
            $plan = $this->getPlan((int)$id);
            if (!$plan) {
                throw new RuntimeException('Plan introuvable ou non autorise.');
            }
        }

        $sql = "DELETE FROM PlanAlimentaire WHERE id_plan = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        header('Location: index.php?page=back&action=listPlans');
        exit;
    }

    public function getPlanWithRepas($id) {
        $plan = $this->getPlan($id);
        if (!$plan) {
            return ['plan' => null, 'repas' => []];
        }

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

    public function getSelectableUtilisateursForCurrentRole() {
        $role = $this->getCurrentRole();
        if ($role === 'Coach') {
            $coachId = $this->resolveSessionUtilisateurId();
            $sql = "SELECT u.id, u.nom
                    FROM utilisateurs u
                    INNER JOIN coach_sportif_assignments a
                        ON a.id_sportif = u.id
                    WHERE a.id_coach = :id_coach
                      AND u.role = 'Sportif'
                      AND u.statut = 'Actif'
                    ORDER BY u.nom ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_coach' => $coachId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = "SELECT id, nom
                FROM utilisateurs
                WHERE role = 'Sportif'
                  AND statut = 'Actif'
                ORDER BY nom ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isUtilisateurAllowedForCurrentRole($idUtilisateur) {
        if ($idUtilisateur <= 0) {
            return false;
        }

        $role = $this->getCurrentRole();
        if ($role === 'Sportif') {
            return $idUtilisateur === $this->resolveSessionUtilisateurId();
        }

        if ($role !== 'Coach') {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM utilisateurs WHERE id = :id AND role = 'Sportif'"
            );
            $stmt->execute(['id' => $idUtilisateur]);
            return (int)$stmt->fetchColumn() > 0;
        }

        $coachId = $this->resolveSessionUtilisateurId();
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM coach_sportif_assignments a
             INNER JOIN utilisateurs u ON u.id = a.id_sportif
             WHERE a.id_coach = :id_coach
               AND a.id_sportif = :id_sportif
               AND u.role = 'Sportif'"
        );
        $stmt->execute([
            'id_coach' => $coachId,
            'id_sportif' => $idUtilisateur,
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function getCurrentRole() {
        $sessionRole = trim((string)($_SESSION['role'] ?? 'Sportif'));
        if (strcasecmp($sessionRole, 'Coach') === 0) {
            return 'Coach';
        }
        if (strcasecmp($sessionRole, 'Admin') === 0) {
            return 'Admin';
        }
        return 'Sportif';
    }

    private function resolveSessionUtilisateurId() {
        $sessionId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        if ($sessionId > 0) {
            return $sessionId;
        }

        $email = trim((string)($_SESSION['user_email'] ?? ''));
        if ($email === '') {
            return 0;
        }

        $stmt = $this->pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $id = $stmt->fetchColumn();
        $resolvedId = $id ? (int)$id : 0;
        if ($resolvedId > 0) {
            $_SESSION['user_id'] = $resolvedId;
        }
        return $resolvedId;
    }
}
