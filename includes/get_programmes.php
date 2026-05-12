<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Parse notes __PROGRAMME__|Libelle|Niveau: …|Fréquence: …|Durée: …|Coach: …|CibleSportif: …
 */
function parse_programme_notes($notes) {
    $out = [
        'libelle_programme' => '',
        'niveau' => '',
        'frequence' => '',
        'duree_semaines' => '',
        'coach' => '',
        'cible_sportif' => ''
    ];
    if ($notes === null || $notes === '' || strpos($notes, '__PROGRAMME__|') !== 0) {
        return $out;
    }
    $rest = substr($notes, strlen('__PROGRAMME__|'));
    $parts = array_map('trim', explode('|', $rest));
    $out['libelle_programme'] = isset($parts[0]) ? $parts[0] : '';

    for ($i = 1; $i < count($parts); $i++) {
        $p = $parts[$i];
        if (preg_match('/^Niveau:\s*(.+)$/iu', $p, $m)) {
            $out['niveau'] = trim($m[1]);
        } elseif (preg_match('/^Fréquence:\s*(.+)$/iu', $p, $m)) {
            $out['frequence'] = trim($m[1]);
        } elseif (preg_match('/^Durée:\s*(.+)$/iu', $p, $m)) {
            $out['duree_semaines'] = trim($m[1]);
        } elseif (preg_match('/^Coach:\s*(.+)$/iu', $p, $m)) {
            $out['coach'] = trim($m[1]);
        } elseif (preg_match('/^CibleSportif:\s*(.+)$/iu', $p, $m)) {
            $out['cible_sportif'] = trim($m[1]);
        }
    }
    return $out;
}

function resolve_session_user_id(PDO $pdo) {
    $sessionId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($sessionId > 0) {
        return $sessionId;
    }

    $email = trim((string)($_SESSION['user_email'] ?? ''));
    if ($email === '') {
        return 0;
    }

    $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $resolved = (int)($stmt->fetchColumn() ?: 0);
    if ($resolved > 0) {
        $_SESSION['user_id'] = $resolved;
    }
    return $resolved;
}

try {
    $database = new Database();
    $pdo = $database->getPDO();

    $role = trim((string)($_SESSION['role'] ?? ''));
    $isCoach = strcasecmp($role, 'Coach') === 0;
    $isSportif = strcasecmp($role, 'Sportif') === 0;
    $coachId = $isCoach ? resolve_session_user_id($pdo) : 0;
    $sportifId = $isSportif ? resolve_session_user_id($pdo) : 0;

    $sql = "SELECT id_entrainement, id_utilisateur, titre, date_entrainement, duree_totale, notes_globales, statut
            FROM entrainements
            WHERE notes_globales LIKE '__PROGRAMME__|%'";

    $params = [];
    if ($isCoach) {
        $sql .= " AND id_utilisateur = :coach_id";
        $params['coach_id'] = $coachId;
    } elseif ($isSportif && $sportifId > 0) {
        // For sportif: fetch all programs, will filter below based on assignment OR explicit target
        // No WHERE restriction here; filter in PHP
    } elseif ($isSportif && $sportifId <= 0) {
        // No resolved sportif in session => return no programme
        $sql .= " AND 1 = 0";
    }

    $sql .= " ORDER BY date_entrainement DESC, id_entrainement DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach ($rows as $row) {
        $meta = parse_programme_notes($row['notes_globales'] ?? '');
        
        // Apply sportif visibility filter
        if ($isSportif && $sportifId > 0) {
            // Check if program is personalized (has explicit target)
            $hasTarget = ($meta['cible_sportif'] !== '');
            $isExplicitTarget = ($hasTarget && (int)$meta['cible_sportif'] === $sportifId);
            
            if ($hasTarget) {
                // Program is personalized: ONLY show to the explicit target sportif
                if (!$isExplicitTarget) {
                    continue;
                }
            } else {
                // Program is general (no target): show if assigned to the coach
                $isAssignedToCoach = false;
                try {
                    $assignStmt = $pdo->prepare("
                        SELECT 1 FROM coach_sportif_assignments
                        WHERE id_coach = ? AND id_sportif = ?
                        LIMIT 1
                    ");
                    $assignStmt->execute([$row['id_utilisateur'], $sportifId]);
                    $isAssignedToCoach = $assignStmt->rowCount() > 0;
                } catch (Exception $e) {
                    $isAssignedToCoach = false;
                }
                
                if (!$isAssignedToCoach) {
                    continue;
                }
            }
        }
        
        $out[] = [
            'id_entrainement' => (int) $row['id_entrainement'],
            'id_utilisateur' => $row['id_utilisateur'],
            'titre' => $row['titre'],
            'date_entrainement' => $row['date_entrainement'],
            'duree_totale' => $row['duree_totale'],
            'notes_globales' => $row['notes_globales'],
            'statut' => $row['statut'],
            'libelle_programme' => $meta['libelle_programme'],
            'niveau' => $meta['niveau'],
            'frequence' => $meta['frequence'],
            'duree_semaines' => $meta['duree_semaines'],
            'coach' => $meta['coach'],
            'cible_sportif' => $meta['cible_sportif']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $out
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
