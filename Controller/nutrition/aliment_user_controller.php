<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../Model/nutrition/Aliment.php';

$alimentModel = new Aliment($pdo);

$filtre_q = $_GET['q'] ?? '';
$filtre_categorie = $_GET['categorie'] ?? '';
$filtre_bio = $_GET['bio'] ?? '';
$filtre_local = $_GET['local'] ?? '';

$aliments = $alimentModel->rechercher($filtre_q, $filtre_categorie, $filtre_bio, $filtre_local);
$categories = $alimentModel->getCategories();
$stats = $alimentModel->statistiques();

require_once __DIR__ . '/../../View/aliments/user.php';
