<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../model/Utilisateur.php';

class ProfilController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function saveProfil($data, $email) {
        if (empty($email)) {
            return array('success' => false, 'message' => 'Non authentifié');
        }

        $user = Utilisateur::findByEmail($this->pdo, $email);
        if (!$user) {
            return array('success' => false, 'message' => 'Utilisateur introuvable');
        }

        // Mise à jour des champs si fournis
        if (isset($data['nom'])) $user->setNom($data['nom']);
        if (isset($data['age'])) $user->setAge((int)$data['age']);
        if (isset($data['poids'])) $user->setPoids((float)$data['poids']);
        if (isset($data['taille'])) $user->setTaille((float)$data['taille']);
        if (isset($data['sport'])) $user->setSport($data['sport']);
        if (isset($data['objectif'])) $user->setObjectif($data['objectif']);
        if (isset($data['niveau'])) $user->setNiveau($data['niveau']);
        if (isset($data['frequence'])) $user->setFrequence((int)$data['frequence']);

        if ($user->update($this->pdo)) {
            $_SESSION['user_nom'] = $user->getNom();
            return array('success' => true, 'message' => 'Profil enregistré avec succès', 'profil' => $user);
        }

        return array('success' => false, 'message' => 'Erreur lors de la sauvegarde');
    }
}

// Automatically process POST requests coming from forms/ajax
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $profilController = new ProfilController($pdo);
    
    if ($_GET['action'] === 'save') {
        // Retrieve JSON payload
        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, TRUE);
        
        // If not json, get POST array
        if (empty($data)) {
            $data = $_POST;
        }

        $result = $profilController->saveProfil($data, $_SESSION['user_email'] ?? '');
        echo json_encode($result);
        exit();
    }

    if ($_GET['action'] === 'deleteAccount') {
        $email = $_SESSION['user_email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);
            exit;
        }

        $user = Utilisateur::findByEmail($pdo, $email);
        if ($user) {
            $success = Utilisateur::delete($pdo, $user->getId());
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
        }
        exit();
    }
}
?>
