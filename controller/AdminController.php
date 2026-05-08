<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../model/Utilisateur.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_email']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'delete') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['id'])) {
        try {
            $success = Utilisateur::delete($pdo, $data['id']);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID manquant']);
    }
} elseif ($action === 'add') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data['email']) && !empty($data['nom']) && !empty($data['password'])) {
        try {
            $user = new Utilisateur(null, $data['nom'], $data['email'], $data['password'], 
                                    $data['age'] ?? 0, 0, 0, $data['sport'] ?? 'Aucun', 
                                    'Non défini', 'Débutant', 1, $data['role'] ?? 'Sportif', $data['statut'] ?? 'Actif');
            // We use setPassword manually because the constructor just assigns the raw pass
            $user->setPassword($data['password']); 
            $success = $user->save($pdo);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
    }
} elseif ($action === 'edit') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data['id']) && !empty($data['email']) && !empty($data['nom'])) {
        try {
            $sql = "UPDATE utilisateurs SET nom = ?, email = ?, role = ?, statut = ?, age = ?, sport_pratique = ? WHERE id = ?";
            $params = [$data['nom'], $data['email'], $data['role'], $data['statut'], $data['age'], $data['sport'], $data['id']];
            
            if (!empty($data['password'])) {
                $sql = "UPDATE utilisateurs SET nom = ?, email = ?, mot_de_passe = ?, role = ?, statut = ?, age = ?, sport_pratique = ? WHERE id = ?";
                $params = [$data['nom'], $data['email'], password_hash($data['password'], PASSWORD_BCRYPT), $data['role'], $data['statut'], $data['age'], $data['sport'], $data['id']];
            }
            
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute($params);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Action inconnue']);
}
