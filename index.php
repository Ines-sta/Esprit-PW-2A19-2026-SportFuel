<?php
/**
 * Point d'entrée et routeur de l'application SportFuel
 */
require_once 'Controller/core/PlanAlimentaireController.php';
require_once 'Controller/core/RepasController.php';
require_once 'Controller/core/role_context.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$basePath = '/Esprit-PW-2A19-2026-SportFuel';
$isBareRootRequest = $requestPath === $basePath . '/' || $requestPath === $basePath . '/index.php';
$isRouterRequest = $requestPath === $basePath . '/' || $requestPath === $basePath . '/index.php';
$hasExplicitRoute = isset($_GET['page']) || isset($_GET['action']) || isset($_GET['id']);
$isAuthenticated = !empty($_SESSION['user_email']);

if ($isRouterRequest && !$isAuthenticated) {
    header('Location: ' . $basePath . '/View/auth/connexion.html');
    exit;
}

// Auto-redirect authenticated users to their role's landing page
if ($isBareRootRequest && !$hasExplicitRoute && $isAuthenticated) {
    $role = sportfuel_current_role();
    if ($role === 'Admin' || $role === 'Coach') {
        header('Location: ' . $basePath . '/index.php?page=dashboard');
        exit;
    }

    header('Location: ' . $basePath . '/index.php?page=home');
    exit;
}

function requireAuthenticatedUser() {
    if (empty($_SESSION['user_email'])) {
        header('Location: /Esprit-PW-2A19-2026-SportFuel/View/auth/connexion.html');
        exit;
    }
}

function resolveSessionUserId(PDO $pdo) {
    $sessionId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($sessionId > 0) {
        return $sessionId;
    }

    $email = trim((string)($_SESSION['user_email'] ?? ''));
    if ($email === '') {
        return 0;
    }

    $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $resolvedId = (int)($stmt->fetchColumn() ?: 0);
    if ($resolvedId > 0) {
        $_SESSION['user_id'] = $resolvedId;
    }

    return $resolvedId;
}

function currentRole() {
    $role = sportfuel_current_role();
    if ($role === 'Admin' || $role === 'Coach') {
        return $role;
    }
    return '';
}

function redirectUnauthorized($message = 'Acces refuse.') {
    $_SESSION['unauthorized_message'] = (string)$message;
    header('Location: /Esprit-PW-2A19-2026-SportFuel/index.php?page=unauthorized');
    exit;
}

function requireBackofficeRole() {
    requireAuthenticatedUser();

    $role = currentRole();
    if ($role !== 'Admin' && $role !== 'Coach') {
        redirectUnauthorized('Acces refuse: role BackOffice requis.');
    }
}

function requireAdminRole() {
    requireAuthenticatedUser();

    $role = currentRole();
    if ($role !== 'Admin') {
        redirectUnauthorized('Acces refuse: role Admin requis.');
    }
}

function requireSportifRole() {
    requireAuthenticatedUser();

    if (sportfuel_is_backoffice_role()) {
        header('Location: ' . sportfuel_canonical_redirect_path(sportfuel_current_role()));
        exit;
    }
}

$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Handle logout before anything else
if ($page === 'auth' && $action === 'logout') {
    session_destroy();
    header('Location: ' . $basePath . '/View/auth/connexion.html');
    exit;
}

$planController = new PlanAlimentaireController();
$repasController = new RepasController();

// Routage
if ($page === 'home') {
    requireSportifRole();
    include 'View/plans/index.php';
} elseif ($page === 'unauthorized') {
    http_response_code(403);
    include 'View/errors/unauthorized.php';
} elseif ($page === 'plans') {
    requireSportifRole();
    include 'View/plans/plans.php';
} elseif ($page === 'detail' && $id) {
    requireSportifRole();
    include 'View/plans/detailPlan.php';
} elseif ($page === 'aliments') {
    requireAuthenticatedUser();
    require 'Controller/nutrition/aliment_controller.php';
} elseif ($page === 'courses') {
    requireAuthenticatedUser();
    require 'Controller/nutrition/course_controller.php';
} elseif ($page === 'coach') {
    $focus = trim((string)($_GET['focus'] ?? ''));
    if (sportfuel_is_backoffice_role()) {
        if ($focus === 'entrainement' || $focus === 'nutrition') {
            $_GET['focus'] = $focus;
        } else {
            unset($_GET['focus']);
        }
        include 'View/coach/admin.php';
    } else {
        if ($focus === 'entrainement' || $focus === 'nutrition') {
            $_GET['focus'] = $focus;
        } else {
            unset($_GET['focus']);
        }
        include 'View/coach/user.php';
    }
} elseif ($page === 'training') {
    $view = trim((string)($_GET['view'] ?? ''));
    if (sportfuel_is_backoffice_role()) {
        if ($view === 'sessions') {
            include 'View/training/admin_sessions.php';
        } else {
            include 'View/training/admin_programs.php';
        }
    } else {
        if ($view === 'history') {
            include 'View/training/user_history.php';
        } else {
            include 'View/training/user_planning.php';
        }
    }
} elseif ($page === 'users') {
    requireAdminRole();
    include 'View/users/index.php';
} elseif ($page === 'profil') {
    requireAuthenticatedUser();
    require_once 'Controller/users/ProfilePageController.php';
    $profilePageController = new ProfilePageController();
    $viewData = $profilePageController->getViewData();
    include 'View/users/profil.php';
} elseif ($page === 'dashboard') {
    requireBackofficeRole();
    $role = sportfuel_current_role();
    if ($role === 'Admin') {
        require_once 'Controller/AdminDashboardController.php';
        $controller = new AdminDashboardController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dashboard_action'])) {
            $result = $controller->processDashboardAction($_POST);
            if (!empty($result['ok'])) {
                $_SESSION['dashboard_notice'] = $result['message'] ?? 'Action effectuée.';
                unset($_SESSION['dashboard_error']);
            } else {
                $_SESSION['dashboard_error'] = $result['message'] ?? 'Action impossible.';
                unset($_SESSION['dashboard_notice']);
            }
            header('Location: ' . $basePath . '/index.php?page=dashboard');
            exit;
        }
        $metrics = $controller->getDashboardMetrics();
        include 'View/dashboard/admin.php';
    } elseif ($role === 'Coach') {
        require_once 'Controller/CoachDashboardController.php';
        $coachId = resolveSessionUserId(Config::getConnexion());
        if ($coachId) {
            $controller = new CoachDashboardController($coachId);
            $metrics = $controller->getDashboardMetrics();
            include 'View/dashboard/coach.php';
        } else {
            http_response_code(401);
            echo 'Erreur: Coach non identifié.';
            exit;
        }
    }
} elseif ($page === 'back') {
    requireBackofficeRole();

    // BackOffice - Plans
    if ($action === 'listPlans') {
        include 'View/plans/admin_listPlans.php';
    } elseif ($action === 'addPlan') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUtilisateur = (int)($_POST['id_utilisateur'] ?? 0);
            if (!$planController->isUtilisateurAllowedForCurrentRole($idUtilisateur)) {
                redirectUnauthorized('Acces refuse: utilisateur cible non autorise.');
            }

            $plan = new PlanAlimentaire(
                null,
                $idUtilisateur,
                $_POST['nom'],
                $_POST['type'],
                $_POST['kcal_cibles'],
                $_POST['semaine'],
                $_POST['date_debut'],
                $_POST['date_fin']
            );
            $planController->addPlan($plan);
        } else {
            include 'View/plans/admin_addPlan.php';
        }
    } elseif ($action === 'updatePlan' && $id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUtilisateur = (int)($_POST['id_utilisateur'] ?? 0);
            if (!$planController->isUtilisateurAllowedForCurrentRole($idUtilisateur)) {
                redirectUnauthorized('Acces refuse: utilisateur cible non autorise.');
            }

            $plan = new PlanAlimentaire(
                $id,
                $idUtilisateur,
                $_POST['nom'],
                $_POST['type'],
                $_POST['kcal_cibles'],
                $_POST['semaine'],
                $_POST['date_debut'],
                $_POST['date_fin']
            );
            $planController->updatePlan($plan);
        } else {
            include 'View/plans/admin_updatePlan.php';
        }
    } elseif ($action === 'deletePlan' && $id) {
        $planController->deletePlan($id);
    }
    // BackOffice - Repas
    elseif ($action === 'listRepas') {
        include 'View/plans/admin_listRepas.php';
    } elseif ($action === 'addRepas') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPlan = (int)($_POST['id_plan'] ?? 0);
            if (!$repasController->isPlanAllowedForCurrentRole($idPlan)) {
                redirectUnauthorized('Acces refuse: plan cible non autorise.');
            }

            $repas = new Repas(
                null,
                $idPlan,
                $_POST['jour'],
                $_POST['type_repas'],
                $_POST['description'],
                $_POST['kcal']
            );
            $repasController->addRepas($repas);
        } else {
            include 'View/plans/admin_addRepas.php';
        }
    } elseif ($action === 'updateRepas' && $id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPlan = (int)($_POST['id_plan'] ?? 0);
            if (!$repasController->isPlanAllowedForCurrentRole($idPlan)) {
                redirectUnauthorized('Acces refuse: plan cible non autorise.');
            }

            $repas = new Repas(
                $id,
                $idPlan,
                $_POST['jour'],
                $_POST['type_repas'],
                $_POST['description'],
                $_POST['kcal']
            );
            $repasController->updateRepas($repas);
        } else {
            include 'View/plans/admin_updateRepas.php';
        }
    } elseif ($action === 'deleteRepas' && $id) {
        $repasController->deleteRepas($id);
    }
}
?>
