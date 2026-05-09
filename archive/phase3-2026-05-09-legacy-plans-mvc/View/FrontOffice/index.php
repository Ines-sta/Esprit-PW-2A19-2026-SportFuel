<?php
/**
 * Page d'accueil FrontOffice — SportFuel
 */
require_once 'Controller/PlanAlimentaireController.php';
$planController = new PlanAlimentaireController();
$allPlans = $planController->listPlans();
$recentPlans = array_slice($allPlans, 0, 3);

// Date du jour en français
$jours_fr = ['Sunday'=>'Dimanche','Monday'=>'Lundi','Tuesday'=>'Mardi','Wednesday'=>'Mercredi','Thursday'=>'Jeudi','Friday'=>'Vendredi','Saturday'=>'Samedi'];
$mois_fr  = ['January'=>'janvier','February'=>'février','March'=>'mars','April'=>'avril','May'=>'mai','June'=>'juin','July'=>'juillet','August'=>'août','September'=>'septembre','October'=>'octobre','November'=>'novembre','December'=>'décembre'];
$today = date('l j F Y');
foreach ($jours_fr as $en => $fr) $today = str_replace($en, $fr, $today);
foreach ($mois_fr  as $en => $fr) $today = str_replace($en, $fr, $today);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportFuel — Accueil</title>
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
        <div class="hero-greeting">Bienvenue sur SportFuel</div>
        <h1>Votre plan du jour est pret</h1>
        <div class="hero-meta">
            Alimentation durable
            <span>&middot;</span>
            Nutrition intelligente
            <span>&middot;</span>
            <?= count($allPlans) ?> plans actifs
        </div>
    </div>
</div>

<div class="content-area">

    <!-- STATS -->
    <div class="front-stats">
        <div class="front-stat-card">
            <div class="fsv fsv-green"><?= array_sum(array_map(fn($p) => $p->getKcalCibles(), $allPlans)) > 0 ? number_format(array_sum(array_map(fn($p) => $p->getKcalCibles(), $allPlans)) / max(1, count($allPlans))) : '0' ?></div>
            <div class="fsl">kcal moyennes / plan</div>
        </div>
        <div class="front-stat-card">
            <div class="fsv fsv-orange"><?= count($allPlans) * 7 ?></div>
            <div class="fsl">kcal brulees (estimation)</div>
        </div>
        <div class="front-stat-card">
            <div class="fsv fsv-blue"><?= round(count($allPlans) * 0.4, 1) ?> kg</div>
            <div class="fsl">CO2 economise</div>
        </div>
    </div>

    <!-- DERNIERS PLANS -->
    <div class="section-label">Plan alimentaire du jour</div>

    <?php if (empty($recentPlans)): ?>
        <div class="empty-state">Aucun plan disponible.</div>
    <?php else: ?>
        <?php foreach ($recentPlans as $plan): ?>
            <div class="meal-section" style="margin-bottom:12px;">
                <div class="meal-section-header">
                    <span><?= htmlspecialchars($plan->getNom()) ?> &mdash; Semaine <?= $plan->getSemaine() ?></span>
                    <span class="badge badge-<?= $plan->getType() ?>"><?= str_replace('_',' ', $plan->getType()) ?></span>
                </div>
                <div class="meal-row">
                    <div class="meal-row-left">
                        <span class="meal-dot dot-green"></span>
                        <?= htmlspecialchars($plan->calculerKcal()) ?> &mdash; <?= htmlspecialchars($plan->afficherPlanJour()) ?>
                    </div>
                    <a href="index.php?page=detail&id=<?= $plan->getIdPlan() ?>" class="btn btn-outline btn-sm">Voir le plan</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include 'View/partials/footer.php'; ?>
</body>
</html>
