<?php
/**
 * BackOffice — Comparaison de plans alimentaires
 * Fonctionnalité avancée intégrée depuis le module Dhya
 */
require_once __DIR__ . '/../../Controller/core/PlanAlimentaireController.php';
require_once __DIR__ . '/../../Controller/core/role_context.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_email'])) {
    header('Location: /Esprit-PW-2A19-2026-SportFuel-main/View/auth/connexion.html');
    exit;
}

// Set active sidebar item
$sidebarActive = 'compare';

$planController = new PlanAlimentaireController();
$plans = $planController->listPlans();

$planA = null;
$planB = null;
$repasA = [];
$repasB = [];

if (isset($_GET['plan_a']) && isset($_GET['plan_b'])) {
    $dataA = $planController->getPlanWithRepas((int)$_GET['plan_a']);
    $dataB = $planController->getPlanWithRepas((int)$_GET['plan_b']);
    $planA = $dataA['plan'];
    $planB = $dataB['plan'];
    $repasA = $dataA['repas'];
    $repasB = $dataB['repas'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparaison de plans — SportFuel</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel-main/public/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php 
        if (sportfuel_is_backoffice_role()) {
            include __DIR__ . '/../partials/backoffice_sidebar.php';
        } else {
            include __DIR__ . '/../partials/sidebar.php';
        }
        ?>
        
        <div class="main-content">
            <div class="page-header">
                <div>
                    <h1>⚖️ Comparaison de plans</h1>
                    <div class="page-date">Analyse côte à côte</div>
                </div>
            </div>

            <div class="content-area">
                <div class="card">
                    <div class="card-header">
                        <h3>Sélectionner 2 plans à comparer</h3>
                    </div>
                    <div style="padding:24px;">
                        <form method="GET">
                            <input type="hidden" name="page" value="plans">
                            <input type="hidden" name="action" value="compare">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Plan A</label>
                                    <select name="plan_a" required style="width:100%;height:40px;padding:0 12px;border:1px solid #ddd;border-radius:8px;">
                                        <option value="">-- Choisir --</option>
                                        <?php foreach ($plans as $p): ?>
                                            <option value="<?= $p->getIdPlan() ?>" <?= isset($_GET['plan_a']) && $_GET['plan_a'] == $p->getIdPlan() ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p->getNom()) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Plan B</label>
                                    <select name="plan_b" required style="width:100%;height:40px;padding:0 12px;border:1px solid #ddd;border-radius:8px;">
                                        <option value="">-- Choisir --</option>
                                        <?php foreach ($plans as $p): ?>
                                            <option value="<?= $p->getIdPlan() ?>" <?= isset($_GET['plan_b']) && $_GET['plan_b'] == $p->getIdPlan() ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p->getNom()) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-accent">Comparer</button>
                        </form>
                    </div>
                </div>

                <?php if ($planA && $planB): ?>
                    <div class="card" style="margin-top:24px;">
                        <div class="card-header">
                            <h3>Résultats de la comparaison</h3>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Critère</th>
                                    <th style="background:#E8F2FD;color:#0066cc;">Plan A</th>
                                    <th style="background:#E8F5EE;color:#52b788;">Plan B</th>
                                    <th>Différence</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Nom</strong></td>
                                    <td><?= htmlspecialchars($planA->getNom()) ?></td>
                                    <td><?= htmlspecialchars($planB->getNom()) ?></td>
                                    <td>—</td>
                                </tr>
                                <tr>
                                    <td><strong>Type</strong></td>
                                    <td><span class="badge"><?= str_replace('_',' ',$planA->getType()) ?></span></td>
                                    <td><span class="badge"><?= str_replace('_',' ',$planB->getType()) ?></span></td>
                                    <td><?= $planA->getType() === $planB->getType() ? '✓ Identique' : '✗ Différent' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Kcal cibles</strong></td>
                                    <td><?= $planA->getKcalCibles() ?> kcal</td>
                                    <td><?= $planB->getKcalCibles() ?> kcal</td>
                                    <td style="<?= $planA->getKcalCibles() > $planB->getKcalCibles() ? 'color:#dc3545;' : 'color:#52b788;' ?>">
                                        <?= abs($planA->getKcalCibles() - $planB->getKcalCibles()) ?> kcal
                                        <?= $planA->getKcalCibles() > $planB->getKcalCibles() ? '↑' : '↓' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Semaine</strong></td>
                                    <td>S<?= $planA->getSemaine() ?></td>
                                    <td>S<?= $planB->getSemaine() ?></td>
                                    <td><?= abs($planA->getSemaine() - $planB->getSemaine()) ?> semaines d'écart</td>
                                </tr>
                                <tr>
                                    <td><strong>Nombre de repas</strong></td>
                                    <td><?= count($repasA) ?> repas</td>
                                    <td><?= count($repasB) ?> repas</td>
                                    <td style="<?= count($repasA) > count($repasB) ? 'color:#52b788;' : 'color:#dc3545;' ?>">
                                        <?= abs(count($repasA) - count($repasB)) ?> repas
                                        <?= count($repasA) > count($repasB) ? '↑' : '↓' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Kcal total planifiés</strong></td>
                                    <td><?= array_sum(array_column($repasA, 'kcal')) ?> kcal</td>
                                    <td><?= array_sum(array_column($repasB, 'kcal')) ?> kcal</td>
                                    <td><?= abs(array_sum(array_column($repasA, 'kcal')) - array_sum(array_column($repasB, 'kcal'))) ?> kcal</td>
                                </tr>
                                <tr>
                                    <td><strong>Durée</strong></td>
                                    <td><?= htmlspecialchars($planA->getDateDebut()) ?> → <?= htmlspecialchars($planA->getDateFin()) ?></td>
                                    <td><?= htmlspecialchars($planB->getDateDebut()) ?> → <?= htmlspecialchars($planB->getDateFin()) ?></td>
                                    <td>—</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php include __DIR__ . '/../partials/footer.php'; ?>
        </div>
    </div>
</body>
</html>
