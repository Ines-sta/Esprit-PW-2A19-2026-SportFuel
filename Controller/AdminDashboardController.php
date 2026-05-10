<?php
/**
 * AdminDashboardController — Fetch metrics for admin dashboard
 */

class AdminDashboardController {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO('mysql:host=localhost;dbname=sportfuel;charset=utf8', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Erreur DB: ' . $e->getMessage());
        }
    }

    /**
     * Get all dashboard metrics
     */
    public function getDashboardMetrics() {
        return [
            'usersByRole' => $this->getUsersByRole(),
            'activeUsersThisMonth' => $this->getActiveUsersThisMonth(),
            'planMetrics' => $this->getPlanMetrics(),
            'trainingMetrics' => $this->getTrainingMetrics(),
            'pendingPublications' => $this->getPendingPublications(),
            'coachAssignments' => $this->getCoachAssignments(),
            'assignmentManagement' => $this->getAssignmentManagementData(),
            'recentUsers' => $this->getRecentUsers(),
            'pendingPublicationsList' => $this->getPendingPublicationsList(),
        ];
    }

    public function processDashboardAction(array $payload) {
        $action = trim((string)($payload['dashboard_action'] ?? ''));
        if ($action === 'assign_sportif') {
            $coachId = (int)($payload['coach_id'] ?? 0);
            $sportifId = (int)($payload['sportif_id'] ?? 0);

            if ($coachId <= 0 || $sportifId <= 0) {
                return ['ok' => false, 'message' => 'Sélectionnez un coach et un sportif valides.'];
            }

            return $this->assignSportifToCoach($coachId, $sportifId);
        }

        if ($action === 'remove_assignment') {
            $assignmentId = (int)($payload['assignment_id'] ?? 0);
            if ($assignmentId <= 0) {
                return ['ok' => false, 'message' => 'Assignment invalide.'];
            }

            return $this->removeAssignment($assignmentId);
        }

        return ['ok' => false, 'message' => 'Action dashboard non prise en charge.'];
    }

    private function getUsersByRole() {
        $stmt = $this->pdo->prepare("SELECT role, COUNT(*) as count FROM utilisateurs GROUP BY role");
        $stmt->execute();
        $usersByRole = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $statsUsersByRole = array_reduce($usersByRole, function($acc, $row) {
            $acc[$row['role']] = $row['count'];
            return $acc;
        }, []);
        
        return [
            'breakdown' => $statsUsersByRole,
            'total' => array_sum(array_column($usersByRole, 'count')),
        ];
    }

    private function getActiveUsersThisMonth() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM utilisateurs WHERE date_inscription IS NOT NULL AND date_inscription >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    }

    private function getPlanMetrics() {
        $stmt = $this->pdo->prepare("SELECT type, COUNT(*) as count FROM PlanAlimentaire GROUP BY type");
        $stmt->execute();
        $plansByType = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'byType' => $plansByType,
            'total' => array_sum(array_column($plansByType, 'count')),
        ];
    }

    private function getTrainingMetrics() {
        $stmt = $this->pdo->prepare("SELECT statut, COUNT(*) as count FROM entrainements GROUP BY statut");
        $stmt->execute();
        $trainingByStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalSessions = array_sum(array_column($trainingByStatus, 'count'));
        $completedSessions = array_filter($trainingByStatus, fn($row) => $row['statut'] === 'Complétée');
        $completedCount = reset($completedSessions)['count'] ?? 0;
        
        return [
            'byStatus' => $trainingByStatus,
            'total' => $totalSessions,
            'completed' => $completedCount,
            'completionRate' => $totalSessions > 0 ? round(($completedCount / $totalSessions) * 100) : 0,
        ];
    }

    private function getPendingPublications() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM publication WHERE statut = 'En attente'");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    }

    private function getCoachAssignments() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT id_coach) as coaches_count, 
                   COUNT(DISTINCT id_sportif) as athletes_count,
                   COUNT(*) as assignments_count
            FROM coach_sportif_assignments
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getAssignmentManagementData() {
        $stmtCoaches = $this->pdo->prepare("
            SELECT id, nom, email, statut
            FROM utilisateurs
            WHERE role = 'Coach'
            ORDER BY statut = 'Inactif', nom ASC
        ");
        $stmtCoaches->execute();
        $coaches = $stmtCoaches->fetchAll(PDO::FETCH_ASSOC);

        $stmtSportifs = $this->pdo->prepare("
            SELECT id, nom, email, sport_pratique, statut
            FROM utilisateurs
            WHERE role = 'Sportif'
            ORDER BY statut = 'Inactif', nom ASC
        ");
        $stmtSportifs->execute();
        $sportifs = $stmtSportifs->fetchAll(PDO::FETCH_ASSOC);

         $stmtAssignments = $this->pdo->prepare("
            SELECT a.id_assignment, a.assigned_at,
                   c.id AS coach_id, c.nom AS coach_nom, c.email AS coach_email,
                   s.id AS sportif_id, s.nom AS sportif_nom, s.email AS sportif_email, s.sport_pratique
            FROM coach_sportif_assignments a
            INNER JOIN utilisateurs c ON c.id = a.id_coach
            INNER JOIN utilisateurs s ON s.id = a.id_sportif
            ORDER BY COALESCE(a.assigned_at, NOW()) DESC
        ");
        $stmtAssignments->execute();
        $assignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);

        return [
            'coaches' => $coaches,
            'sportifs' => $sportifs,
            'assignments' => $assignments,
        ];
    }

    private function assignSportifToCoach($coachId, $sportifId) {
        if (!$this->isUserInRole($coachId, 'Coach')) {
            return ['ok' => false, 'message' => 'Le coach sélectionné est invalide.'];
        }
        if (!$this->isUserInRole($sportifId, 'Sportif')) {
            return ['ok' => false, 'message' => 'Le sportif sélectionné est invalide.'];
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO coach_sportif_assignments (id_coach, id_sportif) VALUES (?, ?)");
            $stmt->execute([$coachId, $sportifId]);
            return ['ok' => true, 'message' => 'Sportif assigné au coach avec succès.'];
        } catch (PDOException $e) {
            if ((string)$e->getCode() === '23000') {
                return ['ok' => false, 'message' => 'Ce sportif est déjà assigné à ce coach.'];
            }
            return ['ok' => false, 'message' => 'Erreur lors de la création de l\'assignment.'];
        }
    }

    private function removeAssignment($assignmentId) {
        $stmt = $this->pdo->prepare("DELETE FROM coach_sportif_assignments WHERE id_assignment = ?");
        $stmt->execute([$assignmentId]);

        if ($stmt->rowCount() < 1) {
            return ['ok' => false, 'message' => 'Assignment introuvable ou déjà supprimé.'];
        }

        return ['ok' => true, 'message' => 'Assignment supprimé avec succès.'];
    }

    private function isUserInRole($userId, $role) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE id = ? AND role = ?");
        $stmt->execute([(int)$userId, (string)$role]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function getRecentUsers() {
        $stmt = $this->pdo->prepare("
            SELECT id, nom, email, role, sport_pratique, statut, date_inscription 
            FROM utilisateurs 
            WHERE date_inscription IS NOT NULL
            ORDER BY date_inscription DESC 
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPendingPublicationsList() {
        $stmt = $this->pdo->prepare("
            SELECT p.id_pub, u.nom, p.text, p.priorite, p.date, p.statut 
            FROM publication p
            JOIN utilisateurs u ON p.id_utilisateur = u.id
            WHERE p.statut = 'En attente'
            ORDER BY p.priorite DESC, p.date DESC
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
