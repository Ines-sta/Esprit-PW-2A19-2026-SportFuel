<?php
/**
 * API endpoint: Get list of sportifs
 * 
 * Query params:
 *   - coach_id (optional): If set, only return sportifs assigned to this coach
 *   - all (optional): If 1, return all sportifs regardless of coach; otherwise coach-filtered or all if not coach role
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=sportfuel;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur base de données']);
    exit;
}

$currentRole = isset($_SESSION['role']) ? trim((string)$_SESSION['role']) : 'Sportif';
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Check request params
$coachId = isset($_GET['coach_id']) ? (int)$_GET['coach_id'] : null;
$getAll = isset($_GET['all']) ? (int)$_GET['all'] : 0;

// Determine what sportifs to return
$sportifs = [];

if ($getAll === 1 || strcasecmp($currentRole, 'Admin') === 0) {
    // Return all sportifs
    $stmt = $pdo->prepare("
        SELECT id, nom, email, sport_pratique, statut
        FROM utilisateurs
        WHERE role = 'Sportif'
        ORDER BY statut = 'Inactif', nom ASC
    ");
    $stmt->execute();
    $sportifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($coachId !== null && $coachId > 0) {
    // Return sportifs assigned to a specific coach
    $stmt = $pdo->prepare("
        SELECT s.id, s.nom, s.email, s.sport_pratique, s.statut
        FROM utilisateurs s
        INNER JOIN coach_sportif_assignments a ON a.id_sportif = s.id
        WHERE a.id_coach = ?
        ORDER BY s.statut = 'Inactif', s.nom ASC
    ");
    $stmt->execute([$coachId]);
    $sportifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (strcasecmp($currentRole, 'Coach') === 0 && $currentUserId > 0) {
    // Coach: return only sportifs assigned to this coach
    $stmt = $pdo->prepare("
        SELECT s.id, s.nom, s.email, s.sport_pratique, s.statut
        FROM utilisateurs s
        INNER JOIN coach_sportif_assignments a ON a.id_sportif = s.id
        WHERE a.id_coach = ?
        ORDER BY s.statut = 'Inactif', s.nom ASC
    ");
    $stmt->execute([$currentUserId]);
    $sportifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Default: return empty list
    $sportifs = [];
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'data' => $sportifs
]);
