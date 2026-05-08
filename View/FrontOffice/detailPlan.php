<?php
/**
 * Detail d'un plan avec repas — FrontOffice
 */
require_once 'Controller/PlanAlimentaireController.php';
$planController = new PlanAlimentaireController();
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php?page=plans'); exit; }

$data = $planController->getPlanWithRepas($id);
$plan = $data['plan'];
$repas = $data['repas'];
if (!$plan) { header('Location: index.php?page=plans'); exit; }

$jours = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
$types = ['petit_dejeuner','dejeuner','diner','collation'];
$typesLabels = ['petit_dejeuner'=>'Petit-dejeuner','dejeuner'=>'Dejeuner','diner'=>'Diner','collation'=>'Collation'];
$dotClass = ['petit_dejeuner'=>'dot-orange','dejeuner'=>'dot-green','diner'=>'dot-blue','collation'=>'dot-gray'];

// Indexation
$repasIndex = [];
$kcalParJour = [];
foreach ($repas as $r) {
    $repasIndex[$r['jour']][$r['type_repas']] = $r;
    $kcalParJour[$r['jour']] = ($kcalParJour[$r['jour']] ?? 0) + $r['kcal'];
}

$reference = 2000;
$pct = min(100, round(($plan->getKcalCibles() / $reference) * 100));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($plan->getNom()) ?> — SportFuel</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<?php include 'View/partials/topbar.php'; ?>

<!-- HERO BANNER -->
<div class="hero-banner">
    <div class="hero-avatar">
        <svg viewBox="0 0 36 36" fill="none">
            <circle cx="18" cy="13" r="7" fill="rgba(255,255,255,0.25)"/>
            <path d="M4 34c0-7.7 6.3-14 14-14s14 6.3 14 14" fill="rgba(255,255,255,0.15)"/>
        </svg>
    </div>
    <div class="hero-text">
        <div class="hero-greeting">Plan nutritionnel</div>
        <h1><?= htmlspecialchars($plan->getNom()) ?></h1>
        <div class="hero-meta">
            <?= str_replace('_',' ', $plan->getType()) ?>
            <span>&middot;</span>
            Semaine <?= $plan->getSemaine() ?>
            <span>&middot;</span>
            <?= $plan->getKcalCibles() ?> kcal cibles
        </div>
    </div>
</div>

<div class="content-area">

    <!-- STATS -->
    <div class="front-stats" style="margin-bottom:20px;">
        <div class="front-stat-card">
            <div class="fsv fsv-green"><?= number_format($plan->getKcalCibles()) ?></div>
            <div class="fsl">kcal cibles / jour</div>
        </div>
        <div class="front-stat-card">
            <div class="fsv fsv-orange"><?= array_sum($kcalParJour) ?></div>
            <div class="fsl">kcal planifiees total</div>
        </div>
        <div class="front-stat-card">
            <div class="fsv fsv-blue"><?= count($repas) ?></div>
            <div class="fsl">repas enregistres</div>
        </div>
    </div>

    <!-- BARRE DE PROGRESSION -->
    <div class="progress-wrap">
        <div class="progress-label">
            <span>Objectif calorique journalier</span>
            <span><?= $plan->getKcalCibles() ?> kcal / <?= $reference ?> kcal reference (<?= $pct ?>%)</span>
        </div>
        <div class="progress-track">
            <div class="progress-fill" style="width:<?= $pct ?>%"></div>
        </div>
    </div>

    <!-- REPAS PAR JOUR -->
    <div class="section-label">Plan alimentaire du jour</div>

    <?php if (empty($repas)): ?>
        <div class="empty-state">Aucun repas enregistre pour ce plan.</div>
    <?php else: ?>
        <?php foreach ($jours as $jour): ?>
            <?php if (!isset($repasIndex[$jour])) continue; ?>
            <div class="meal-section">
                <div class="meal-section-header">
                    <span><?= $jour ?> &mdash; <?= isset($kcalParJour[$jour]) ? $kcalParJour[$jour] . ' kcal' : '' ?></span>
                    <span class="badge-bio">Bio - Local</span>
                </div>
                <?php foreach ($types as $type): ?>
                    <?php if (!isset($repasIndex[$jour][$type])) continue; ?>
                    <?php $r = $repasIndex[$jour][$type]; ?>
                    <div class="meal-row">
                        <div class="meal-row-left">
                            <span class="meal-dot <?= $dotClass[$type] ?>"></span>
                            <span><?= $typesLabels[$type] ?> &mdash; <?= htmlspecialchars($r['description']) ?></span>
                        </div>
                        <div class="meal-row-kcal"><?= $r['kcal'] ?> kcal</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="margin-top:20px;">
        <a href="index.php?page=plans" class="btn btn-outline">&larr; Retour aux plans</a>
    </div>

</div>

<?php include 'View/partials/footer.php'; ?>
</body>
</html>
