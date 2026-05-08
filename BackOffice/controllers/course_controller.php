<?php
// Controller: Course (BackOffice — CRUD + recherche + stats + utilisateurs dummy)

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cloudinary.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Aliment.php';

// ===== Liste utilisateurs (hardcodée — équipe SportFuel)
// Sera remplacée par l'entité User réelle quand elle sera prête.
$users = [
    ['id' => 1, 'nom' => 'Ines Sta'],
    ['id' => 2, 'nom' => 'Maram Bendoulet'],
    ['id' => 3, 'nom' => 'Yassine Bellagha'],
    ['id' => 4, 'nom' => 'Dhya Laabidi'],
    ['id' => 5, 'nom' => 'Bayrem Hariz'],
];
function getUserName($users, $id) {
    foreach ($users as $u) {
        if ((int)$u['id'] === (int)$id) return $u['nom'];
    }
    return 'Utilisateur #' . (int)$id;
}
function isUserIdValid($users, $id) {
    foreach ($users as $u) {
        if ((int)$u['id'] === (int)$id) return true;
    }
    return false;
}

$courseModel = new Course($pdo);
$alimentModel = new Aliment($pdo);
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

$statutsAutorises = ['Non démarrée', 'En cours', 'Complétée'];
$unitesAutorisees = Course::$unitesAutorisees;

// ===== Actions =====
switch ($action) {

    case 'ajouter':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_utilisateur = intval($_POST['id_utilisateur'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $date = trim($_POST['date'] ?? '');
            $statut = trim($_POST['statut'] ?? '');

            if (empty($nom))                                $error = "Le nom de la liste est obligatoire.";
            elseif (strlen($nom) > 150)                     $error = "Le nom ne doit pas dépasser 150 caractères.";
            elseif (!isUserIdValid($users, $id_utilisateur))$error = "Veuillez sélectionner un utilisateur.";
            elseif (empty($date))                           $error = "La date est obligatoire.";
            elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $error = "Format de date invalide (AAAA-MM-JJ).";
            elseif (empty($statut))                         $error = "Le statut est obligatoire.";
            elseif (!in_array($statut, $statutsAutorises))  $error = "Statut invalide.";
            else {
                $uploadErr = '';
                $image_url = cloudinary_handle_upload($_FILES['image'] ?? null, CLOUDINARY_FOLDER . '/courses', $uploadErr);
                if ($uploadErr !== '') {
                    $error = $uploadErr;
                } else {
                    $courseModel->genererListeCourses($id_utilisateur, $nom, $date, $statut, [], $image_url);
                    header('Location: course_controller.php?success=ajout');
                    exit;
                }
            }
        }
        break;

    case 'modifier':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $id_utilisateur = intval($_POST['id_utilisateur'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $date = trim($_POST['date'] ?? '');
            $statut = trim($_POST['statut'] ?? '');

            if ($id <= 0)                                   $error = "Course invalide.";
            elseif (empty($nom))                            $error = "Le nom de la liste est obligatoire.";
            elseif (strlen($nom) > 150)                     $error = "Le nom ne doit pas dépasser 150 caractères.";
            elseif (!isUserIdValid($users, $id_utilisateur))$error = "Veuillez sélectionner un utilisateur.";
            elseif (empty($date))                           $error = "La date est obligatoire.";
            elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $error = "Format de date invalide (AAAA-MM-JJ).";
            elseif (empty($statut))                         $error = "Le statut est obligatoire.";
            elseif (!in_array($statut, $statutsAutorises))  $error = "Statut invalide.";
            else {
                $uploadErr = '';
                $image_url = cloudinary_handle_upload($_FILES['image'] ?? null, CLOUDINARY_FOLDER . '/courses', $uploadErr);
                if ($uploadErr !== '') {
                    $error = $uploadErr;
                } else {
                    $courseModel->modifier($id, $id_utilisateur, $nom, $date, $statut, $image_url);
                    header('Location: course_controller.php?action=voir&id=' . $id . '&success=modif');
                    exit;
                }
            }
        }
        break;

    case 'supprimer':
        // Accepte GET (legacy) ou POST (recommandé). $_REQUEST = $_GET ∪ $_POST
        $id = intval($_REQUEST['id'] ?? 0);
        if ($id > 0) {
            $courseModel->supprimer($id);
            header('Location: course_controller.php?success=suppr');
            exit;
        }
        break;

    case 'ajouter_article':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_course = intval($_POST['id_course'] ?? 0);
            $id_aliment = intval($_POST['id_aliment'] ?? 0);
            $quantite = floatval($_POST['quantite'] ?? 0);
            $unite = trim($_POST['unite'] ?? 'g');

            if ($id_course <= 0)                                $error = "Course invalide.";
            elseif ($id_aliment <= 0)                           $error = "Veuillez sélectionner un aliment.";
            elseif ($quantite <= 0)                             $error = "La quantité doit être un nombre positif.";
            elseif (!in_array($unite, $unitesAutorisees, true)) $error = "Unité invalide.";
            else {
                $courseModel->ajouterArticle($id_course, $id_aliment, $quantite, $unite);
                header('Location: course_controller.php?action=voir&id=' . $id_course . '&success=article_ajout');
                exit;
            }
        }
        break;

    case 'supprimer_article':
        $id_course = intval($_REQUEST['id_course'] ?? 0);
        $id_aliment = intval($_REQUEST['id_aliment'] ?? 0);
        if ($id_course > 0 && $id_aliment > 0) {
            $courseModel->supprimerArticle($id_course, $id_aliment);
            header('Location: course_controller.php?action=voir&id=' . $id_course . '&success=article_suppr');
            exit;
        }
        break;
}

// Messages flash
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'ajout':         $success = "Liste de courses créée avec succès."; break;
        case 'modif':         $success = "Liste de courses modifiée avec succès."; break;
        case 'suppr':         $success = "Liste supprimée avec succès."; break;
        case 'article_ajout': $success = "Article ajouté à la liste."; break;
        case 'article_suppr': $success = "Article retiré de la liste."; break;
    }
}

// ===== Filtres GET =====
$filtre_q         = $_GET['q'] ?? '';
$filtre_statut    = $_GET['statut_filtre'] ?? '';
$filtre_user      = $_GET['user_filtre'] ?? '';
$filtre_date_min  = $_GET['date_min'] ?? '';
$filtre_date_max  = $_GET['date_max'] ?? '';

$courses  = $courseModel->rechercher($filtre_q, $filtre_statut, $filtre_user, $filtre_date_min, $filtre_date_max);
$aliments = $alimentModel->listerTout();
$stats    = $courseModel->statistiques();

// Vue détail
$courseDetail = null;
if (isset($_GET['action']) && $_GET['action'] === 'voir' && isset($_GET['id'])) {
    $courseDetail = $courseModel->getById(intval($_GET['id']));
}

// Édition
$courseEdit = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $courseEdit = $courseModel->getById(intval($_GET['id']));
}

require_once __DIR__ . '/../views/courses/courses.php';
