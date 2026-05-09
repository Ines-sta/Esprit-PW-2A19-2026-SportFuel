<?php
/**
 * Liste de tous les plans — FrontOffice
 */
require_once 'Controller/PlanAlimentaireController.php';
$planController = new PlanAlimentaireController();
$plans = $planController->listPlans();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plans alimentaires — SportFuel</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<?php include 'View/partials/topbar.php'; ?>

<div class="page-header">
    <div>
        <h1>Plans alimentaires</h1>
        <div class="page-date"><?= count($plans) ?> plan(s) disponible(s)</div>
    </div>
</div>

<div class="content-area">

    <!-- FILTRES -->
    <div class="filter-bar">
        <button class="filter-btn active" data-filter="all">Tous</button>
        <button class="filter-btn" data-filter="prise_de_masse">Prise de masse</button>
        <button class="filter-btn" data-filter="perte_de_poids">Perte de poids</button>
        <button class="filter-btn" data-filter="maintien">Maintien</button>
        <button class="filter-btn" data-filter="endurance">Endurance</button>
        <input type="text" id="searchInput" class="search-input" style="width:220px;height:34px;" placeholder="Rechercher un plan...">
    </div>

    <?php if (empty($plans)): ?>
        <div class="empty-state">Aucun plan disponible.</div>
    <?php else: ?>
        <div class="plans-grid" id="plansGrid">
            <?php foreach ($plans as $plan): ?>
                <div class="plan-card"
                     data-type="<?= htmlspecialchars($plan->getType()) ?>"
                     data-nom="<?= strtolower(htmlspecialchars($plan->getNom())) ?>">
                    <span class="badge badge-<?= htmlspecialchars($plan->getType()) ?>">
                        <?= str_replace('_', ' ', htmlspecialchars($plan->getType())) ?>
                    </span>
                    <h3><?= htmlspecialchars($plan->getNom()) ?></h3>
                    <div class="plan-kcal"><?= htmlspecialchars($plan->getKcalCibles()) ?> <span>kcal / jour</span></div>
                    <div class="plan-meta">
                        Semaine <?= htmlspecialchars($plan->getSemaine()) ?>
                        &nbsp;&middot;&nbsp;
                        <?= htmlspecialchars($plan->getDateDebut()) ?> &rarr; <?= htmlspecialchars($plan->getDateFin()) ?>
                    </div>
                    <div class="plan-card-footer">
                        <a href="index.php?page=detail&id=<?= $plan->getIdPlan() ?>" class="btn btn-outline btn-sm">Voir detail</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div id="noResults" style="display:none;" class="empty-state">Aucun plan ne correspond.</div>
    <?php endif; ?>

</div>

<?php include 'View/partials/footer.php'; ?>

<script>
const filterBtns  = document.querySelectorAll('.filter-btn');
const searchInput = document.getElementById('searchInput');
const cards       = document.querySelectorAll('.plan-card');
const noResults   = document.getElementById('noResults');
let currentFilter = 'all', currentSearch = '';

function applyFilters() {
    let visible = 0;
    cards.forEach(card => {
        const matchFilter = currentFilter === 'all' || card.dataset.type === currentFilter;
        const matchSearch = card.dataset.nom.includes(currentSearch.toLowerCase());
        const show = matchFilter && matchSearch;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
}

filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.dataset.filter;
        applyFilters();
    });
});

searchInput.addEventListener('keyup', () => { currentSearch = searchInput.value; applyFilters(); });
</script>
</body>
</html>
