<?php

class ProfileSummaryService {
    public static function getCoachSummary(PDO $pdo, int $coachId): array {
        $summary = [
            'assigned_athletes' => 0,
            'pending_publications' => 0,
            'recent_workouts' => 0,
            'completion_rate' => 0,
            'athletes' => []
        ];

        if ($coachId <= 0) {
            return $summary;
        }

        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT id_sportif) FROM coach_sportif_assignments WHERE id_coach = ?");
        $stmt->execute([$coachId]);
        $summary['assigned_athletes'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM publication p JOIN coach_sportif_assignments c ON c.id_sportif = p.id_utilisateur WHERE c.id_coach = ? AND p.statut = 'En attente'");
        $stmt->execute([$coachId]);
        $summary['pending_publications'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM entrainements e JOIN coach_sportif_assignments c ON c.id_sportif = e.id_utilisateur WHERE c.id_coach = ? AND e.date_entrainement >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute([$coachId]);
        $summary['recent_workouts'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) AS total, SUM(CASE WHEN e.statut = 'Complétée' THEN 1 ELSE 0 END) AS completed FROM entrainements e JOIN coach_sportif_assignments c ON c.id_sportif = e.id_utilisateur WHERE c.id_coach = ?");
        $stmt->execute([$coachId]);
        $ratio = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'completed' => 0];
        $total = (int)($ratio['total'] ?? 0);
        $completed = (int)($ratio['completed'] ?? 0);
        $summary['completion_rate'] = $total > 0 ? (int)round(($completed / $total) * 100) : 0;

        $stmt = $pdo->prepare("SELECT u.nom, u.sport_pratique, u.niveau FROM coach_sportif_assignments c JOIN utilisateurs u ON u.id = c.id_sportif WHERE c.id_coach = ? ORDER BY c.assigned_at DESC LIMIT 5");
        $stmt->execute([$coachId]);
        $summary['athletes'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return $summary;
    }

    public static function getAdminSummary(PDO $pdo): array {
        $summary = [
            'total_users' => 0,
            'coach_count' => 0,
            'sportif_count' => 0,
            'pending_publications' => 0,
            'assignments_count' => 0,
            'active_this_month' => 0,
            'recent_users' => []
        ];

        $summary['total_users'] = (int)$pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
        $summary['coach_count'] = (int)$pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'Coach'")->fetchColumn();
        $summary['sportif_count'] = (int)$pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'Sportif'")->fetchColumn();
        $summary['pending_publications'] = (int)$pdo->query("SELECT COUNT(*) FROM publication WHERE statut = 'En attente'")->fetchColumn();
        $summary['assignments_count'] = (int)$pdo->query("SELECT COUNT(*) FROM coach_sportif_assignments")->fetchColumn();
        $summary['active_this_month'] = (int)$pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE date_inscription IS NOT NULL AND date_inscription >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

        $stmt = $pdo->query("SELECT nom, email, role, statut FROM utilisateurs ORDER BY id DESC LIMIT 6");
        $summary['recent_users'] = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

        return $summary;
    }

    public static function getSummaryByRole(PDO $pdo, string $role, int $userId): array {
        if (strcasecmp($role, 'Coach') === 0) {
            return self::getCoachSummary($pdo, $userId);
        }

        if (strcasecmp($role, 'Admin') === 0) {
            return self::getAdminSummary($pdo);
        }

        return [];
    }
}
