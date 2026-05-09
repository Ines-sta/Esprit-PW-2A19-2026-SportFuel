<?php
/**
 * Point d'entrée et routeur de l'application SportFuel
 */
require_once 'Controller/PlanAlimentaireController.php';
require_once 'Controller/RepasController.php';

$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

$planController = new PlanAlimentaireController();
$repasController = new RepasController();

/**
 * Include the first existing template from preferred then legacy paths.
 */
function includeFirst(array $paths) {
    foreach ($paths as $path) {
        if (file_exists($path)) {
            include $path;
            return;
        }
    }
    throw new RuntimeException('Template introuvable: ' . implode(', ', $paths));
}

// Routage
if ($page === 'home') {
    includeFirst([
        'FrontOffice/views/plans/index.php',
        'View/FrontOffice/index.php'
    ]);
} elseif ($page === 'plans') {
    includeFirst([
        'FrontOffice/views/plans/plans.php',
        'View/FrontOffice/plans.php'
    ]);
} elseif ($page === 'detail' && $id) {
    includeFirst([
        'FrontOffice/views/plans/detailPlan.php',
        'View/FrontOffice/detailPlan.php'
    ]);
} elseif ($page === 'back') {
    // BackOffice - Plans
    if ($action === 'listPlans') {
        includeFirst([
            'BackOffice/views/plans/listPlans.php',
            'View/BackOffice/listPlans.php'
        ]);
    } elseif ($action === 'addPlan') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $plan = new PlanAlimentaire(
                null,
                $_POST['id_utilisateur'],
                $_POST['nom'],
                $_POST['type'],
                $_POST['kcal_cibles'],
                $_POST['semaine'],
                $_POST['date_debut'],
                $_POST['date_fin']
            );
            $planController->addPlan($plan);
        } else {
            includeFirst([
                'BackOffice/views/plans/addPlan.php',
                'View/BackOffice/addPlan.php'
            ]);
        }
    } elseif ($action === 'updatePlan' && $id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $plan = new PlanAlimentaire(
                $id,
                $_POST['id_utilisateur'],
                $_POST['nom'],
                $_POST['type'],
                $_POST['kcal_cibles'],
                $_POST['semaine'],
                $_POST['date_debut'],
                $_POST['date_fin']
            );
            $planController->updatePlan($plan);
        } else {
            includeFirst([
                'BackOffice/views/plans/updatePlan.php',
                'View/BackOffice/updatePlan.php'
            ]);
        }
    } elseif ($action === 'deletePlan' && $id) {
        $planController->deletePlan($id);
    }
    // BackOffice - Repas
    elseif ($action === 'listRepas') {
        includeFirst([
            'BackOffice/views/plans/listRepas.php',
            'View/BackOffice/listRepas.php'
        ]);
    } elseif ($action === 'addRepas') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $repas = new Repas(
                null,
                $_POST['id_plan'],
                $_POST['jour'],
                $_POST['type_repas'],
                $_POST['description'],
                $_POST['kcal']
            );
            $repasController->addRepas($repas);
        } else {
            includeFirst([
                'BackOffice/views/plans/addRepas.php',
                'View/BackOffice/addRepas.php'
            ]);
        }
    } elseif ($action === 'updateRepas' && $id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $repas = new Repas(
                $id,
                $_POST['id_plan'],
                $_POST['jour'],
                $_POST['type_repas'],
                $_POST['description'],
                $_POST['kcal']
            );
            $repasController->updateRepas($repas);
        } else {
            includeFirst([
                'BackOffice/views/plans/updateRepas.php',
                'View/BackOffice/updateRepas.php'
            ]);
        }
    } elseif ($action === 'deleteRepas' && $id) {
        $repasController->deleteRepas($id);
    }
}
?>
