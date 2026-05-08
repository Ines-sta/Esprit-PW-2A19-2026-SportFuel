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

// Routage
if ($page === 'home') {
    include 'View/FrontOffice/index.php';
} elseif ($page === 'plans') {
    include 'View/FrontOffice/plans.php';
} elseif ($page === 'detail' && $id) {
    include 'View/FrontOffice/detailPlan.php';
} elseif ($page === 'back') {
    // BackOffice - Plans
    if ($action === 'listPlans') {
        include 'View/BackOffice/listPlans.php';
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
            include 'View/BackOffice/addPlan.php';
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
            include 'View/BackOffice/updatePlan.php';
        }
    } elseif ($action === 'deletePlan' && $id) {
        $planController->deletePlan($id);
    }
    // BackOffice - Repas
    elseif ($action === 'listRepas') {
        include 'View/BackOffice/listRepas.php';
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
            include 'View/BackOffice/addRepas.php';
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
            include 'View/BackOffice/updateRepas.php';
        }
    } elseif ($action === 'deleteRepas' && $id) {
        $repasController->deleteRepas($id);
    }
}
?>
