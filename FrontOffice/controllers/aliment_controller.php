<?php
// Controller FO: Aliment (lecture seule + recherche + stats)

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cloudinary.php';
require_once __DIR__ . '/../models/Aliment.php';

$alimentModel = new Aliment($pdo);

// Filtres GET
$filtre_q         = $_GET['q'] ?? '';
$filtre_categorie = $_GET['categorie'] ?? '';
$filtre_bio       = $_GET['bio'] ?? '';
$filtre_local     = $_GET['local'] ?? '';

$aliments   = $alimentModel->rechercher($filtre_q, $filtre_categorie, $filtre_bio, $filtre_local);
$categories = $alimentModel->getCategories();
$stats      = $alimentModel->statistiques();

require_once __DIR__ . '/../views/aliments/aliments.php';
