<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cloudinary.php';
require_once __DIR__ . '/../../Model/nutrition/CourseAdmin.php';
require_once __DIR__ . '/../../Model/nutrition/Aliment.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getSessionRole() {
    $role = trim((string)($_SESSION['role'] ?? ''));
    if (strcasecmp($role, 'Coach') === 0) return 'Coach';
    if (strcasecmp($role, 'Admin') === 0) return 'Admin';
    return '';
}

function resolveCurrentUtilisateurId($pdo) {
    $email = trim((string)($_SESSION['user_email'] ?? ''));
    if ($email === '') return 0;
    $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $id = $stmt->fetchColumn();
    return $id ? (int)$id : 0;
}

function loadSelectableUsers($pdo, $role, $currentUtilisateurId) {
    if ($role === 'Coach') {
        $stmt = $pdo->prepare(
            "SELECT u.id, u.nom
             FROM utilisateurs u
             INNER JOIN coach_sportif_assignments a ON a.id_sportif = u.id
             WHERE a.id_coach = :id_coach
               AND u.role = 'Sportif'
               AND u.statut = 'Actif'
             ORDER BY u.nom ASC"
        );
        $stmt->execute(['id_coach' => $currentUtilisateurId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $stmt = $pdo->query(
        "SELECT id, nom
         FROM utilisateurs
         WHERE role = 'Sportif'
           AND statut = 'Actif'
         ORDER BY nom ASC"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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

function isCourseAllowed($courseModel, $courseId, $allowedUserIds) {
    if ((int)$courseId <= 0) return false;
    return $courseModel->getById((int)$courseId, $allowedUserIds) !== null;
}

$role = getSessionRole();
if ($role !== 'Admin' && $role !== 'Coach') {
    http_response_code(403);
    echo 'Acces refuse: role BackOffice requis.';
    exit;
}

$currentUtilisateurId = resolveCurrentUtilisateurId($pdo);
$users = loadSelectableUsers($pdo, $role, $currentUtilisateurId);
$allowedUserIds = array_map(static function ($user) {
    return (int)$user['id'];
}, $users);

$courseModel = new Course($pdo);
$alimentModel = new Aliment($pdo);
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$editSource = isset($_GET['from']) && $_GET['from'] === 'list' ? 'list' : 'detail';
$error = '';
$success = '';
$statutsAutorises = ['Non démarrée', 'En cours', 'Complétée'];
$unitesAutorisees = Course::$unitesAutorisees;

switch ($action) {
    case 'ajouter':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_utilisateur = intval($_POST['id_utilisateur'] ?? 0);
            $nom = trim($_POST['nom'] ?? '');
            $date = trim($_POST['date'] ?? '');
            $statut = trim($_POST['statut'] ?? '');

            if (empty($nom)) $error = 'Le nom de la liste est obligatoire.';
            elseif (strlen($nom) > 150) $error = 'Le nom ne doit pas dépasser 150 caractères.';
            elseif (!isUserIdValid($users, $id_utilisateur)) $error = 'Veuillez sélectionner un utilisateur.';
            elseif (empty($date)) $error = 'La date est obligatoire.';
            elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $error = 'Format de date invalide (AAAA-MM-JJ).';
            elseif (empty($statut)) $error = 'Le statut est obligatoire.';
            elseif (!in_array($statut, $statutsAutorises, true)) $error = 'Statut invalide.';
            else {
                $uploadErr = '';
                $image_url = cloudinary_handle_upload($_FILES['image'] ?? null, CLOUDINARY_FOLDER . '/courses', $uploadErr);
                if ($uploadErr !== '') {
                    $error = $uploadErr;
                } else {
                    $courseModel->genererListeCourses($id_utilisateur, $nom, $date, $statut, [], $image_url);
                    header('Location: /Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&success=ajout');
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
            $editSource = (isset($_POST['edit_source']) && $_POST['edit_source'] === 'list') ? 'list' : 'detail';

            if ($id <= 0) $error = 'Course invalide.';
            elseif (!isCourseAllowed($courseModel, $id, $allowedUserIds)) $error = 'Acces refuse a cette course.';
            elseif (empty($nom)) $error = 'Le nom de la liste est obligatoire.';
            elseif (strlen($nom) > 150) $error = 'Le nom ne doit pas dépasser 150 caractères.';
            elseif (!isUserIdValid($users, $id_utilisateur)) $error = 'Veuillez sélectionner un utilisateur.';
            elseif (empty($date)) $error = 'La date est obligatoire.';
            elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $error = 'Format de date invalide (AAAA-MM-JJ).';
            elseif (empty($statut)) $error = 'Le statut est obligatoire.';
            elseif (!in_array($statut, $statutsAutorises, true)) $error = 'Statut invalide.';
            else {
                $uploadErr = '';
                $image_url = cloudinary_handle_upload($_FILES['image'] ?? null, CLOUDINARY_FOLDER . '/courses', $uploadErr);
                if ($uploadErr !== '') {
                    $error = $uploadErr;
                } else {
                    $courseModel->modifier($id, $id_utilisateur, $nom, $date, $statut, $image_url);
                    if ($editSource === 'list') {
                        header('Location: /Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&success=modif');
                    } else {
                        header('Location: /Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&action=voir&id=' . $id . '&success=modif');
                    }
                    exit;
                }
            }
        }
        break;

    case 'supprimer':
        $id = intval($_REQUEST['id'] ?? 0);
        if ($id > 0 && isCourseAllowed($courseModel, $id, $allowedUserIds)) {
            $courseModel->supprimer($id);
            header('Location: /Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&success=suppr');
            exit;
        } elseif ($id > 0) {
            $error = 'Acces refuse a cette course.';
        }
        break;

    case 'ajouter_article':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_course = intval($_POST['id_course'] ?? 0);
            $id_aliment = intval($_POST['id_aliment'] ?? 0);
            $quantite = floatval($_POST['quantite'] ?? 0);
            $unite = trim($_POST['unite'] ?? 'g');

            if ($id_course <= 0) $error = 'Course invalide.';
            elseif (!isCourseAllowed($courseModel, $id_course, $allowedUserIds)) $error = 'Acces refuse a cette course.';
            elseif ($id_aliment <= 0) $error = 'Veuillez sélectionner un aliment.';
            elseif ($quantite <= 0) $error = 'La quantité doit être un nombre positif.';
            elseif (!in_array($unite, $unitesAutorisees, true)) $error = 'Unité invalide.';
            else {
                $courseModel->ajouterArticle($id_course, $id_aliment, $quantite, $unite);
                header('Location: /Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&action=voir&id=' . $id_course . '&success=article_ajout');
                exit;
            }
        }
        break;

    case 'supprimer_article':
        $id_course = intval($_REQUEST['id_course'] ?? 0);
        $id_aliment = intval($_REQUEST['id_aliment'] ?? 0);
        if ($id_course > 0 && $id_aliment > 0 && isCourseAllowed($courseModel, $id_course, $allowedUserIds)) {
            $courseModel->supprimerArticle($id_course, $id_aliment);
            header('Location: /Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&action=voir&id=' . $id_course . '&success=article_suppr');
            exit;
        } elseif ($id_course > 0 && $id_aliment > 0) {
            $error = 'Acces refuse a cette course.';
        }
        break;
}

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'ajout': $success = 'Liste de courses créée avec succès.'; break;
        case 'modif': $success = 'Liste de courses modifiée avec succès.'; break;
        case 'suppr': $success = 'Liste supprimée avec succès.'; break;
        case 'article_ajout': $success = 'Article ajouté à la liste.'; break;
        case 'article_suppr': $success = 'Article retiré de la liste.'; break;
    }
}

$filtre_q = $_GET['q'] ?? '';
$filtre_statut = $_GET['statut_filtre'] ?? '';
$filtre_user = $_GET['user_filtre'] ?? '';
$filtre_date_min = $_GET['date_min'] ?? '';
$filtre_date_max = $_GET['date_max'] ?? '';

if ($filtre_user !== '' && !in_array((int)$filtre_user, $allowedUserIds, true)) {
    $filtre_user = '';
}

$courses = $courseModel->rechercher($filtre_q, $filtre_statut, $filtre_user, $filtre_date_min, $filtre_date_max, $allowedUserIds);
$aliments = $alimentModel->listerTout();
$stats = $courseModel->statistiques($allowedUserIds);

$courseDetail = null;
if ((isset($_GET['action']) && $_GET['action'] === 'voir') && isset($_GET['id'])) {
    $courseDetail = $courseModel->getById(intval($_GET['id']), $allowedUserIds);
    if ($courseDetail === null) $error = 'Acces refuse a cette course.';
}

$courseEdit = null;
if ((isset($_GET['action']) && $_GET['action'] === 'edit') && isset($_GET['id'])) {
    $courseEdit = $courseModel->getById(intval($_GET['id']), $allowedUserIds);
    if ($courseEdit === null) $error = 'Acces refuse a cette course.';
}

require_once __DIR__ . '/../../View/courses/admin.php';
