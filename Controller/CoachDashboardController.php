<?php
/**
 * CoachDashboardController — Fetch metrics for coach dashboard
 */

class CoachDashboardController {
    private $pdo;
    private $coachId;

    public function __construct($coachId) {
        try {
            $this->pdo = new PDO('mysql:host=localhost;dbname=sportfuel;charset=utf8', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Erreur DB: ' . $e->getMessage());
        }
        
        $this->coachId = $coachId;
        if (!$this->coachId) {
            http_response_code(401);
            die('Erreur: Coach non identifié.');
        }
    }

    /**
     * Get all dashboard metrics for this coach
     */
    public function getDashboardMetrics() {
        return [
            'assignedAthletes' => $this->getAssignedAthletes(),
            'activePlans' => $this->getActivePlans(),
            'thisWeekStats' => $this->getThisWeekStats(),
            'workoutCompletionThisWeek' => $this->getWorkoutCompletionThisWeek(),
            'adherenceRate' => $this->getAdherenceRate(),
            'pendingApprovals' => $this->getPendingApprovals(),
            'athletes' => $this->getAthletesList(),
            'pendingPublications' => $this->getPendingPublicationsList(),
            'recentWorkouts' => $this->getRecentWorkoutsList(),
        ];
    }

    private function getAssignedAthletes() {
        $stmt = $this->pdo->prepare("SELECT COUNT(DISTINCT id_sportif) as count FROM coach_sportif_assignments WHERE id_coach = ?");
        $stmt->execute([$this->coachId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    }

    private function getActivePlans() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT p.id_plan) as count 
            FROM PlanAlimentaire p
            JOIN coach_sportif_assignments c ON p.id_utilisateur = c.id_sportif
            WHERE c.id_coach = ?
        ");
        $stmt->execute([$this->coachId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    }

    private function getThisWeekStats() {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(DISTINCT e.id_entrainement) as workouts_count,
                SUM(e.duree_totale) as total_duration
            FROM entrainements e
            JOIN coach_sportif_assignments c ON e.id_utilisateur = c.id_sportif
            WHERE c.id_coach = ? AND e.date_entrainement >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute([$this->coachId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $workoutsCount = $stats['workouts_count'] ?? 0;
        $totalDuration = $stats['total_duration'] ?? 0;
        
        return [
            'workouts_count' => $workoutsCount,
            'total_duration' => $totalDuration,
            'average_duration' => $workoutsCount > 0 ? round($totalDuration / $workoutsCount) : 0,
        ];
    }

    private function getWorkoutCompletionThisWeek() {
        $stmt = $this->pdo->prepare("
            SELECT e.statut, COUNT(*) as count 
            FROM entrainements e
            JOIN coach_sportif_assignments c ON e.id_utilisateur = c.id_sportif
            WHERE c.id_coach = ? AND e.date_entrainement >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY e.statut
        ");
        $stmt->execute([$this->coachId]);
        $recentWorkouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalRecentWorkouts = array_sum(array_column($recentWorkouts, 'count'));
        $completedWorkouts = array_filter($recentWorkouts, fn($row) => $row['statut'] === 'Complétée');
        $completedCount = reset($completedWorkouts)['count'] ?? 0;
        
        return [
            'total' => $totalRecentWorkouts,
            'completed' => $completedCount,
            'rate' => $totalRecentWorkouts > 0 ? round(($completedCount / $totalRecentWorkouts) * 100) : 0,
        ];
    }

    private function getAdherenceRate() {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(DISTINCT e.id_entrainement) as total_workouts,
                SUM(CASE WHEN e.statut = 'Complétée' THEN 1 ELSE 0 END) as completed_workouts
            FROM entrainements e
            JOIN coach_sportif_assignments c ON e.id_utilisateur = c.id_sportif
            WHERE c.id_coach = ?
        ");
        $stmt->execute([$this->coachId]);
        $adherenceData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $totalAssignedWorkouts = $adherenceData['total_workouts'] ?? 0;
        $completedAllTime = $adherenceData['completed_workouts'] ?? 0;
        
        return [
            'total' => $totalAssignedWorkouts,
            'completed' => $completedAllTime,
            'rate' => $totalAssignedWorkouts > 0 ? round(($completedAllTime / $totalAssignedWorkouts) * 100) : 0,
        ];
    }

    private function getPendingApprovals() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM publication 
            WHERE id_utilisateur IN (SELECT id_sportif FROM coach_sportif_assignments WHERE id_coach = ?)
            AND statut = 'En attente'
        ");
        $stmt->execute([$this->coachId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    }

    private function getAthletesList() {
        $stmt = $this->pdo->prepare("
              SELECT u.id, u.nom, u.email, u.sport_pratique, u.niveau, u.statut, u.photo_profil_url, c.assigned_at
            FROM coach_sportif_assignments c
            JOIN utilisateurs u ON c.id_sportif = u.id
            WHERE c.id_coach = ?
            ORDER BY COALESCE(c.assigned_at, NOW()) DESC
            LIMIT 15
        ");
        $stmt->execute([$this->coachId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPendingPublicationsList() {
        $stmt = $this->pdo->prepare("
            SELECT p.id_pub, u.nom, u.photo_profil_url, p.text, p.priorite, p.date, p.statut
            FROM publication p
            JOIN utilisateurs u ON p.id_utilisateur = u.id
            JOIN coach_sportif_assignments c ON u.id = c.id_sportif
            WHERE c.id_coach = ? AND p.statut = 'En attente'
            ORDER BY p.priorite DESC, p.date DESC
            LIMIT 10
        ");
        $stmt->execute([$this->coachId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getRecentWorkoutsList() {
        $stmt = $this->pdo->prepare("
            SELECT e.id_entrainement, u.nom, u.photo_profil_url, e.titre, e.date_entrainement, e.duree_totale, e.statut
            FROM entrainements e
            JOIN utilisateurs u ON e.id_utilisateur = u.id
            JOIN coach_sportif_assignments c ON u.id = c.id_sportif
            WHERE c.id_coach = ?
            ORDER BY e.date_entrainement DESC
            LIMIT 15
        ");
        $stmt->execute([$this->coachId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
