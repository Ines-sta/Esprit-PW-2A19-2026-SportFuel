<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/users/Utilisateur.php';
require_once __DIR__ . '/../shared/ProfileSummaryService.php';
require_once __DIR__ . '/../core/role_context.php';

class ProfilePageController {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    public function getViewData(): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_email'])) {
            header('Location: /Esprit-PW-2A19-2026-SportFuel-main/View/auth/connexion.html');
            exit;
        }

        $user = Utilisateur::findByEmail($this->pdo, (string)$_SESSION['user_email']);
        if (!$user) {
            session_destroy();
            header('Location: /Esprit-PW-2A19-2026-SportFuel-main/View/auth/connexion.html');
            exit;
        }

        $role = sportfuel_current_role();
        $isCoachRole = ($role === 'Coach');
        $isAdminRole = ($role === 'Admin');
        $isBackofficeRole = $isCoachRole || $isAdminRole;
        $isSportifRole = !$isBackofficeRole;

        $coachStats = [
            'assigned_athletes' => 0,
            'pending_publications' => 0,
            'recent_workouts' => 0,
            'completion_rate' => 0,
            'athletes' => []
        ];

        $adminStats = [
            'total_users' => 0,
            'coach_count' => 0,
            'sportif_count' => 0,
            'pending_publications' => 0,
            'assignments_count' => 0,
            'active_this_month' => 0,
            'recent_users' => []
        ];

        if ($isCoachRole) {
            $coachStats = ProfileSummaryService::getCoachSummary($this->pdo, (int)$user->getId());
        } elseif ($isAdminRole) {
            $adminStats = ProfileSummaryService::getAdminSummary($this->pdo);
        }

        $imc = ($user->getTaille() > 0)
            ? round($user->getPoids() / (($user->getTaille() / 100) ** 2), 1)
            : 0;

        $imcMax = 30.0;
        $imcProgress = ($imc > 0)
            ? max(0, min(100, ($imc / $imcMax) * 100))
            : 0;

        $objectifPoids = $this->computeObjectifPoids(
            (float)$user->getPoids(),
            (float)$user->getTaille(),
            (string)$user->getObjectif()
        );

        $objectifPoidsProgress = ($objectifPoids['target_weight'] > 0 && (float)$user->getPoids() > 0)
            ? max(
                0,
                min(
                    100,
                    (1 - (abs((float)$user->getPoids() - $objectifPoids['target_weight']) / max((float)$user->getPoids(), $objectifPoids['target_weight']))) * 100
                )
            )
            : 0;

        return [
            'user' => $user,
            'imc' => $imc,
            'imcMax' => $imcMax,
            'imcProgress' => round($imcProgress, 1),
            'objectifPoidsCible' => $objectifPoids['target_weight'],
            'objectifPoidsLabel' => $objectifPoids['label'],
            'objectifPoidsProgress' => round($objectifPoidsProgress, 1),
            'photoProfilUrl' => (string)($user->getPhotoProfilUrl() ?? ''),
            'initial' => $this->buildInitials($user->getNom()),
            'roleLabel' => strtolower($role),
            'isBackofficeRole' => $isBackofficeRole,
            'isCoachRole' => $isCoachRole,
            'isAdminRole' => $isAdminRole,
            'isSportifRole' => $isSportifRole,
            'coachStats' => $coachStats,
            'adminStats' => $adminStats,
            'sportifActivities' => $isSportifRole ? $this->getSportifActivities((int)$user->getId()) : []
        ];
    }

    private function computeObjectifPoids(float $poids, float $tailleCm, string $objectif): array {
        if ($poids <= 0 || $tailleCm <= 0) {
            return ['target_weight' => 0.0, 'label' => 'Objectif non défini'];
        }

        $tailleM = $tailleCm / 100;
        $objectifNorm = mb_strtolower(trim($objectif), 'UTF-8');

        // Approximation métier simple: IMC cible dépend de l'objectif sélectionné.
        $bmiTarget = 22.5;
        if ($objectifNorm === 'perte de poids' || $objectifNorm === 'légèreté') {
            $bmiTarget = 21.5;
        } elseif ($objectifNorm === 'prise de masse') {
            $bmiTarget = 24.0;
        } elseif ($objectifNorm === 'endurance') {
            $bmiTarget = 22.0;
        } elseif ($objectifNorm === 'performance') {
            $bmiTarget = 23.0;
        }

        $targetWeight = round($bmiTarget * ($tailleM ** 2), 1);
        return [
            'target_weight' => $targetWeight,
            'label' => $targetWeight > 0 ? ($targetWeight . ' kg cible') : 'Objectif non défini',
        ];
    }

    private function buildInitials(string $name): string {
        $base = trim($name);
        if ($base === '') {
            return 'SF';
        }

        $parts = preg_split('/\s+/u', $base);
        $initials = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $initials .= mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
            if (mb_strlen($initials, 'UTF-8') >= 2) {
                break;
            }
        }

        return $initials !== '' ? $initials : 'SF';
    }

    private function getSportifActivities(int $userId): array {
        $activities = [];

        try {
            $stmt = $this->pdo->prepare(" 
                SELECT 'plan' AS event_type,
                       p.date_debut AS event_date,
                       p.nom AS title,
                       CONCAT(REPLACE(p.type, '_', ' '), ' · ', p.kcal_cibles, ' kcal cibles') AS detail
                FROM PlanAlimentaire p
                WHERE p.id_utilisateur = :uid

                UNION ALL

                SELECT 'entrainement' AS event_type,
                       e.date_entrainement AS event_date,
                       COALESCE(NULLIF(e.titre, ''), 'Séance entraînement') AS title,
                       CONCAT(COALESCE(e.duree_totale, 0), ' min · ', COALESCE(e.statut, 'N/A')) AS detail
                FROM entrainements e
                WHERE e.id_utilisateur = :uid

                UNION ALL

                SELECT 'course' AS event_type,
                       c.date AS event_date,
                       COALESCE(NULLIF(c.nom, ''), 'Liste de courses') AS title,
                       CONCAT('Statut: ', COALESCE(c.statut, 'N/A')) AS detail
                FROM course c
                WHERE c.id_utilisateur = :uid

                ORDER BY event_date DESC
                LIMIT 6
            ");
            $stmt->execute([':uid' => $userId]);
            $rawItems = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($rawItems as $item) {
                $eventType = (string)($item['event_type'] ?? '');
                $icon = '📌';
                $label = 'Activité';
                if ($eventType === 'plan') {
                    $icon = '🥗';
                    $label = 'Plan alimentaire';
                } elseif ($eventType === 'entrainement') {
                    $icon = '🏃';
                    $label = 'Entraînement';
                } elseif ($eventType === 'course') {
                    $icon = '🛒';
                    $label = 'Liste de courses';
                }

                $activities[] = [
                    'icon' => $icon,
                    'label' => $label,
                    'title' => (string)($item['title'] ?? '-'),
                    'detail' => (string)($item['detail'] ?? ''),
                    'relative_day' => $this->toRelativeDayLabel((string)($item['event_date'] ?? ''))
                ];
            }
        } catch (Exception $e) {
            return [];
        }

        return $activities;
    }

    private function toRelativeDayLabel(string $dateValue): string {
        if ($dateValue === '') {
            return '-';
        }

        try {
            $date = new DateTime($dateValue);
            $today = new DateTime('today');
            $target = new DateTime($date->format('Y-m-d'));
            $diffDays = (int)$today->diff($target)->format('%r%a');

            if ($diffDays === 0) {
                return "Aujourd'hui";
            }
            if ($diffDays === -1) {
                return 'Hier';
            }
            if ($diffDays < -1) {
                return 'Il y a ' . abs($diffDays) . 'j';
            }

            return 'Dans ' . $diffDays . 'j';
        } catch (Exception $e) {
            return '-';
        }
    }
}
