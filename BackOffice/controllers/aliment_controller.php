<?php
// Controller: Aliment (BackOffice — CRUD + recherche + stats)

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cloudinary.php';
require_once __DIR__ . '/../models/Aliment.php';

$alimentModel = new Aliment($pdo);
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

// ===== Actions =====
switch ($action) {

    case 'ajouter':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $categorie = trim($_POST['categorie'] ?? '');
            $kcal_portion = floatval($_POST['kcal_portion'] ?? 0);
            $co2_impact = floatval($_POST['co2_impact'] ?? 0);
            $prix_unitaire = floatval($_POST['prix_unitaire'] ?? 0);
            $est_bio = isset($_POST['est_bio']) ? 1 : 0;
            $est_local = isset($_POST['est_local']) ? 1 : 0;

            if (empty($nom))                       $error = "Le nom de l'aliment est obligatoire.";
            elseif (strlen($nom) > 150)            $error = "Le nom ne doit pas dépasser 150 caractères.";
            elseif (empty($categorie))             $error = "La catégorie est obligatoire.";
            elseif (strlen($categorie) > 100)      $error = "La catégorie ne doit pas dépasser 100 caractères.";
            elseif ($kcal_portion <= 0)            $error = "Les calories doivent être un nombre positif.";
            elseif ($co2_impact < 0)               $error = "L'impact CO₂ doit être un nombre positif.";
            elseif ($prix_unitaire <= 0)           $error = "Le prix unitaire doit être un nombre positif.";
            else {
                $uploadErr = '';
                $image_url = cloudinary_handle_upload($_FILES['image'] ?? null, CLOUDINARY_FOLDER . '/aliments', $uploadErr);
                if ($uploadErr !== '') {
                    $error = $uploadErr;
                } else {
                    $alimentModel->ajouter($nom, $categorie, $kcal_portion, $co2_impact, $prix_unitaire, $est_bio, $est_local, $image_url);
                    header('Location: aliment_controller.php?success=ajout');
                    exit;
                }
            }
        }
        break;

    case 'modifier':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $categorie = trim($_POST['categorie'] ?? '');
            $kcal_portion = floatval($_POST['kcal_portion'] ?? 0);
            $co2_impact = floatval($_POST['co2_impact'] ?? 0);
            $prix_unitaire = floatval($_POST['prix_unitaire'] ?? 0);
            $est_bio = isset($_POST['est_bio']) ? 1 : 0;
            $est_local = isset($_POST['est_local']) ? 1 : 0;

            if ($id <= 0)                          $error = "Aliment invalide.";
            elseif (empty($nom))                   $error = "Le nom de l'aliment est obligatoire.";
            elseif (strlen($nom) > 150)            $error = "Le nom ne doit pas dépasser 150 caractères.";
            elseif (empty($categorie))             $error = "La catégorie est obligatoire.";
            elseif (strlen($categorie) > 100)      $error = "La catégorie ne doit pas dépasser 100 caractères.";
            elseif ($kcal_portion <= 0)            $error = "Les calories doivent être un nombre positif.";
            elseif ($co2_impact < 0)               $error = "L'impact CO₂ doit être un nombre positif.";
            elseif ($prix_unitaire <= 0)           $error = "Le prix unitaire doit être un nombre positif.";
            else {
                $uploadErr = '';
                $image_url = cloudinary_handle_upload($_FILES['image'] ?? null, CLOUDINARY_FOLDER . '/aliments', $uploadErr);
                if ($uploadErr !== '') {
                    $error = $uploadErr;
                } else {
                    // $image_url null → le modèle conserve l'image existante (COALESCE)
                    $alimentModel->modifier($id, $nom, $categorie, $kcal_portion, $co2_impact, $prix_unitaire, $est_bio, $est_local, $image_url);
                    header('Location: aliment_controller.php?success=modif');
                    exit;
                }
            }
        }
        break;

    case 'supprimer':
        // Accepte GET (legacy) ou POST (recommandé)
        $id = intval($_REQUEST['id'] ?? 0);
        if ($id > 0) {
            $alimentModel->supprimer($id);
            header('Location: aliment_controller.php?success=suppr');
            exit;
        }
        break;
}

// Messages flash
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'ajout': $success = "Aliment ajouté avec succès."; break;
        case 'modif': $success = "Aliment modifié avec succès."; break;
        case 'suppr': $success = "Aliment supprimé avec succès."; break;
    }
}

// ===== Filtres GET =====
$filtre_q         = $_GET['q'] ?? '';
$filtre_categorie = $_GET['categorie'] ?? '';
$filtre_bio       = $_GET['bio'] ?? '';
$filtre_local     = $_GET['local'] ?? '';

$aliments   = $alimentModel->rechercher($filtre_q, $filtre_categorie, $filtre_bio, $filtre_local);
$categories = $alimentModel->getCategories();
$stats      = $alimentModel->statistiques();

// Aliment à modifier
$alimentEdit = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $alimentEdit = $alimentModel->getById(intval($_GET['id']));
}

require_once __DIR__ . '/../views/aliments/aliments.php';
