<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cloudinary.php';
require_once __DIR__ . '/../../Model/nutrition/CourseUser.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function resolveCurrentSportif(PDO $pdo) {
    $sessionId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($sessionId > 0) {
        $stmt = $pdo->prepare('SELECT id, nom, role FROM utilisateurs WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $sessionId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && strcasecmp((string)($user['role'] ?? ''), 'Sportif') === 0) {
            return ['id' => (int)$user['id'], 'nom' => (string)$user['nom']];
        }
    }

    $email = trim((string)($_SESSION['user_email'] ?? ''));
    if ($email !== '') {
        $stmt = $pdo->prepare('SELECT id, nom, role FROM utilisateurs WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && strcasecmp((string)($user['role'] ?? ''), 'Sportif') === 0) {
            $_SESSION['user_id'] = (int)$user['id'];
            return ['id' => (int)$user['id'], 'nom' => (string)$user['nom']];
        }
    }

    return null;
}

$currentSportif = resolveCurrentSportif($pdo);
if (!$currentSportif) {
    http_response_code(403);
    echo 'Acces refuse: utilisateur sportif requis.';
    exit;
}

$currentUserId = (int)$currentSportif['id'];
$currentUserName = (string)$currentSportif['nom'];

$courseModel = new Course($pdo);
$success = '';
$error = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

$optimizerInput = [
    'nom' => 'Liste optimisée',
    'kcal_target' => 2000,
    'budget_max' => 20,
    'prefer_bio' => 1,
    'prefer_local' => 1,
];
$optimizerPreview = null;

switch ($action) {
    case 'toggle_achete':
        $id_course = intval($_GET['id_course'] ?? 0);
        $id_aliment = intval($_GET['id_aliment'] ?? 0);
        if ($id_course > 0 && $id_aliment > 0) {
            $course = $courseModel->getById($id_course);
            if ($course && (int)$course['id_utilisateur'] === $currentUserId) {
                $courseModel->marquerAchete($id_course, $id_aliment);
                header('Location: /Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&id=' . $id_course . '&success=achat');
                exit;
            }
        }
        break;

    case 'optimiser_preview':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $optimizerInput = [
                'nom' => trim($_POST['nom'] ?? 'Liste optimisée'),
                'kcal_target' => floatval($_POST['kcal_target'] ?? 0),
                'budget_max' => floatval($_POST['budget_max'] ?? 0),
                'prefer_bio' => ((string)($_POST['prefer_bio'] ?? '0') === '1') ? 1 : 0,
                'prefer_local' => ((string)($_POST['prefer_local'] ?? '0') === '1') ? 1 : 0,
            ];

            if ($optimizerInput['nom'] === '') $error = 'Le nom de la liste est obligatoire.';
            elseif (strlen($optimizerInput['nom']) > 150) $error = 'Le nom ne doit pas dépasser 150 caractères.';
            elseif ($optimizerInput['kcal_target'] <= 0) $error = 'La cible kcal doit être un nombre positif.';
            elseif ($optimizerInput['budget_max'] <= 0) $error = 'Le budget max doit être un nombre positif.';
            else {
                try {
                    $optimizerPreview = $courseModel->genererApercuOptimise(
                        $optimizerInput['kcal_target'],
                        $optimizerInput['budget_max'],
                        $optimizerInput['prefer_bio'],
                        $optimizerInput['prefer_local']
                    );
                } catch (Throwable $e) {
                    $error = 'Impossible de générer l\'aperçu. Vérifiez la migration prix_unitaire.';
                }
            }
        }
        break;

    case 'optimiser_create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $optimizerInput = [
                'nom' => trim($_POST['nom'] ?? 'Liste optimisée'),
                'kcal_target' => floatval($_POST['kcal_target'] ?? 0),
                'budget_max' => floatval($_POST['budget_max'] ?? 0),
                'prefer_bio' => ((string)($_POST['prefer_bio'] ?? '0') === '1') ? 1 : 0,
                'prefer_local' => ((string)($_POST['prefer_local'] ?? '0') === '1') ? 1 : 0,
            ];

            if ($optimizerInput['nom'] === '') $error = 'Le nom de la liste est obligatoire.';
            elseif (strlen($optimizerInput['nom']) > 150) $error = 'Le nom ne doit pas dépasser 150 caractères.';
            elseif ($optimizerInput['kcal_target'] <= 0) $error = 'La cible kcal doit être un nombre positif.';
            elseif ($optimizerInput['budget_max'] <= 0) $error = 'Le budget max doit être un nombre positif.';
            else {
                try {
                    $optimizerPreview = $courseModel->genererApercuOptimise(
                        $optimizerInput['kcal_target'],
                        $optimizerInput['budget_max'],
                        $optimizerInput['prefer_bio'],
                        $optimizerInput['prefer_local']
                    );

                    if (empty($optimizerPreview['items'])) {
                        $error = 'Aucun panier optimisé possible avec ces paramètres.';
                    } else {
                        $newCourseId = $courseModel->creerCourseOptimisee(
                            $currentUserId,
                            $optimizerInput['nom'],
                            $optimizerPreview['items']
                        );

                        if ($newCourseId > 0) {
                            header('Location: /Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&id=' . $newCourseId . '&success=optimise');
                            exit;
                        }
                        $error = 'Échec de création de la liste optimisée.';
                    }
                } catch (Throwable $e) {
                    $error = 'Impossible de créer la liste optimisée. Vérifiez la migration prix_unitaire.';
                }
            }
        }
        break;
}

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'achat') {
        $success = "Statut d'achat mis à jour.";
    } elseif ($_GET['success'] === 'optimise') {
        $success = 'Liste optimisée créée avec succès.';
    }
}

$filtre_q = $_GET['q'] ?? '';
$filtre_statut = $_GET['statut_filtre'] ?? '';
$filtre_user = $currentUserId;

$courses = $courseModel->rechercher($filtre_q, $filtre_statut, $filtre_user);
$stats = $courseModel->statistiques($currentUserId);

$courseDetail = null;
if (isset($_GET['id'])) {
    $course = $courseModel->getById(intval($_GET['id']));
    if ($course && (int)$course['id_utilisateur'] === $currentUserId) {
        $courseDetail = $course;
    }
}

$showOptimizerModal = in_array($action, ['optimiser_preview', 'optimiser_create'], true);

require_once __DIR__ . '/../../View/courses/user.php';
