<?php
// Controller FO: Course (lecture + toggle achat — pas de CRUD admin)

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cloudinary.php';
require_once __DIR__ . '/../models/Course.php';

// Liste utilisateurs hardcodée (mêmes 5 que le BackOffice — équipe SportFuel)
$users = [
    ['id' => 1, 'nom' => 'Ines Sta'],
    ['id' => 2, 'nom' => 'Maram Bendoulet'],
    ['id' => 3, 'nom' => 'Yassine Bellagha'],
    ['id' => 4, 'nom' => 'Dhya Laabidi'],
    ['id' => 5, 'nom' => 'Bayrem Hariz'],
];

// Simulation de session FrontOffice: utilisateur connecté = Ines (id=1)
$currentUserId = 1;
function getUserName($users, $id) {
    foreach ($users as $u) {
        if ((int)$u['id'] === (int)$id) return $u['nom'];
    }
    return 'Utilisateur #' . (int)$id;
}

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

// Seule action utilisateur autorisée : toggle achat
switch ($action) {
    case 'toggle_achete':
        $id_course = intval($_GET['id_course'] ?? 0);
        $id_aliment = intval($_GET['id_aliment'] ?? 0);
        if ($id_course > 0 && $id_aliment > 0) {
            $course = $courseModel->getById($id_course);
            if ($course && (int)$course['id_utilisateur'] === $currentUserId) {
                $courseModel->marquerAchete($id_course, $id_aliment);
                header('Location: course_controller.php?id=' . $id_course . '&success=achat');
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
                'prefer_bio' => isset($_POST['prefer_bio']) ? 1 : 0,
                'prefer_local' => isset($_POST['prefer_local']) ? 1 : 0,
            ];

            if ($optimizerInput['nom'] === '') {
                $error = "Le nom de la liste est obligatoire.";
            } elseif (strlen($optimizerInput['nom']) > 150) {
                $error = "Le nom ne doit pas dépasser 150 caractères.";
            } elseif ($optimizerInput['kcal_target'] <= 0) {
                $error = "La cible kcal doit être un nombre positif.";
            } elseif ($optimizerInput['budget_max'] <= 0) {
                $error = "Le budget max doit être un nombre positif.";
            } else {
                try {
                    $optimizerPreview = $courseModel->genererApercuOptimise(
                        $optimizerInput['kcal_target'],
                        $optimizerInput['budget_max'],
                        $optimizerInput['prefer_bio'],
                        $optimizerInput['prefer_local']
                    );
                } catch (Throwable $e) {
                    $error = "Impossible de générer l'aperçu. Vérifiez la migration prix_unitaire.";
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
                'prefer_bio' => intval($_POST['prefer_bio'] ?? 0),
                'prefer_local' => intval($_POST['prefer_local'] ?? 0),
            ];

            if ($optimizerInput['nom'] === '') {
                $error = "Le nom de la liste est obligatoire.";
            } elseif (strlen($optimizerInput['nom']) > 150) {
                $error = "Le nom ne doit pas dépasser 150 caractères.";
            } elseif ($optimizerInput['kcal_target'] <= 0) {
                $error = "La cible kcal doit être un nombre positif.";
            } elseif ($optimizerInput['budget_max'] <= 0) {
                $error = "Le budget max doit être un nombre positif.";
            } else {
                try {
                    $optimizerPreview = $courseModel->genererApercuOptimise(
                        $optimizerInput['kcal_target'],
                        $optimizerInput['budget_max'],
                        $optimizerInput['prefer_bio'],
                        $optimizerInput['prefer_local']
                    );

                    if (empty($optimizerPreview['items'])) {
                        $error = "Aucun panier optimisé possible avec ces paramètres.";
                    } else {
                        $newCourseId = $courseModel->creerCourseOptimisee(
                            $currentUserId,
                            $optimizerInput['nom'],
                            $optimizerPreview['items']
                        );

                        if ($newCourseId > 0) {
                            header('Location: course_controller.php?id=' . $newCourseId . '&success=optimise');
                            exit;
                        }
                        $error = "Échec de création de la liste optimisée.";
                    }
                } catch (Throwable $e) {
                    $error = "Impossible de créer la liste optimisée. Vérifiez la migration prix_unitaire.";
                }
            }
        }
        break;
}

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'achat') {
        $success = "Statut d'achat mis à jour.";
    } elseif ($_GET['success'] === 'optimise') {
        $success = "Liste optimisée créée avec succès.";
    }
}

// Filtres GET
$filtre_q       = $_GET['q'] ?? '';
$filtre_statut  = $_GET['statut_filtre'] ?? '';

$filtre_user = $currentUserId;

$courses = $courseModel->rechercher($filtre_q, $filtre_statut, $filtre_user);
$stats   = $courseModel->statistiques($currentUserId);

// Vue détail
$courseDetail = null;
if (isset($_GET['id'])) {
    $course = $courseModel->getById(intval($_GET['id']));
    if ($course && (int)$course['id_utilisateur'] === $currentUserId) {
        $courseDetail = $course;
    }
}

$currentUserName = getUserName($users, $currentUserId);
$showOptimizerModal = in_array($action, ['optimiser_preview', 'optimiser_create'], true);

require_once __DIR__ . '/../views/courses/courses.php';
