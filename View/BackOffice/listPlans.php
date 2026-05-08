<?php
/**
 * BackOffice — Liste des plans alimentaires
 */
require_once 'Controller/PlanAlimentaireController.php';
$planController = new PlanAlimentaireController();
$plans = $planController->listPlans();

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
    <title>Plans alimentaires — SportFuel Admin</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'View/partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>Vue d'ensemble</h1>
                <div class="page-date"><?= $today ?></div>
            </div>
            <a href="index.php?page=back&action=addPlan" class="btn btn-accent">+ Nouveau plan</a>
        </div>

        <div class="content-area">

            <!-- STATS -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-value"><?= count($plans) ?></div>
                    <div class="stat-label">Plans actifs</div>
                    <div class="stat-delta green">+<?= max(1, round(count($plans) * 0.12)) ?> ce mois</div>
                </div>
                <div class="stat-card">
                    <?php
                    $totalKcal = array_sum(array_map(fn($p) => $p->getKcalCibles(), $plans));
                    $avgKcal = count($plans) > 0 ? round($totalKcal / count($plans)) : 0;
                    ?>
                    <div class="stat-value"><?= number_format($avgKcal) ?></div>
                    <div class="stat-label">Kcal moyennes / plan</div>
                    <div class="stat-delta green">+8% ce mois</div>
                </div>
                <div class="stat-card">
                    <?php
                    $types = array_count_values(array_map(fn($p) => $p->getType(), $plans));
                    $topType = !empty($types) ? array_key_first($types) : '—';
                    ?>
                    <div class="stat-value"><?= $types[$topType] ?? 0 ?></div>
                    <div class="stat-label"><?= str_replace('_',' ', $topType) ?></div>
                    <div class="stat-delta orange">+3 cette semaine</div>
                </div>
                <div class="stat-card">
                    <?php
                    require_once 'Controller/RepasController.php';
                    $repasController = new RepasController();
                    $totalRepas = count($repasController->listRepas());
                    ?>
                    <div class="stat-value"><?= $totalRepas ?></div>
                    <div class="stat-label">Repas enregistres</div>
                    <div class="stat-delta green">Total</div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="card">
                <div class="card-header">
                    <h3>Derniers plans enregistres</h3>
                    <a href="index.php?page=back&action=addPlan" class="btn btn-outline btn-sm">+ Ajouter</a>
                </div>
                <div class="search-wrap">
                    <input type="text" id="searchInput" class="search-input" placeholder="Rechercher...">
                </div>
                <?php if (empty($plans)): ?>
                    <div class="empty-state">Aucun plan enregistre.</div>
                <?php else: ?>
                    <table class="data-table" id="plansTable">
                        <thead>
                            <tr>
                                <th class="sortable" data-col="0">Nom <span class="sort-icon">&#8597;</span></th>
                                <th class="sortable" data-col="1">Type <span class="sort-icon">&#8597;</span></th>
                                <th class="sortable" data-col="2">Kcal cibles <span class="sort-icon">&#8597;</span></th>
                                <th class="sortable" data-col="3">Semaine <span class="sort-icon">&#8597;</span></th>
                                <th class="sortable" data-col="4">Date debut <span class="sort-icon">&#8597;</span></th>
                                <th class="sortable" data-col="5">Date fin <span class="sort-icon">&#8597;</span></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plans as $plan): ?>
                                <tr data-search="<?= strtolower(htmlspecialchars($plan->getNom())) ?>">
                                    <td><strong><?= htmlspecialchars($plan->getNom()) ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?= htmlspecialchars($plan->getType()) ?>">
                                            <?= str_replace('_', ' ', htmlspecialchars($plan->getType())) ?>
                                        </span>
                                    </td>
                                    <td data-val="<?= $plan->getKcalCibles() ?>"><?= htmlspecialchars($plan->getKcalCibles()) ?> kcal</td>
                                    <td class="td-muted" data-val="<?= $plan->getSemaine() ?>">S<?= htmlspecialchars($plan->getSemaine()) ?></td>
                                    <td class="td-muted"><?= htmlspecialchars($plan->getDateDebut()) ?></td>
                                    <td class="td-muted"><?= htmlspecialchars($plan->getDateFin()) ?></td>
                                    <td>
                                        <div class="td-actions">
                                            <a href="index.php?page=back&action=updatePlan&id=<?= $plan->getIdPlan() ?>" class="btn btn-outline btn-sm">Modifier</a>
                                            <a href="index.php?page=back&action=listRepas&id_plan=<?= $plan->getIdPlan() ?>" class="btn btn-outline btn-sm">Repas</a>
                                            <a href="index.php?page=back&action=deletePlan&id=<?= $plan->getIdPlan() ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Supprimer ce plan et tous ses repas ?')">Supprimer</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </div>
        <?php include 'View/partials/footer.php'; ?>
    </div>
</div>

<script>
// ── SEARCH ──────────────────────────────────────
document.getElementById('searchInput').addEventListener('keyup', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#plansTable tbody tr').forEach(row => {
        row.style.display = row.dataset.search.includes(q) ? '' : 'none';
    });
});

// ── SORT ─────────────────────────────────────────
let sortState = { col: null, dir: 'asc' };

document.querySelectorAll('.sortable').forEach(th => {
    th.style.cursor = 'pointer';
    th.style.userSelect = 'none';

    th.addEventListener('click', function() {
        const col = parseInt(this.dataset.col);

        if (sortState.col === col) {
            sortState.dir = sortState.dir === 'asc' ? 'desc' : 'asc';
        } else {
            sortState.col = col;
            sortState.dir = 'asc';
        }

        // Update icons
        document.querySelectorAll('.sortable .sort-icon').forEach(ic => ic.textContent = '⇅');
        this.querySelector('.sort-icon').textContent = sortState.dir === 'asc' ? '↑' : '↓';

        const tbody = document.querySelector('#plansTable tbody');
        const rows  = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const cellA = a.cells[col];
            const cellB = b.cells[col];

            // Use data-val if present (numeric cols), else text
            const valA = cellA.dataset.val !== undefined ? parseFloat(cellA.dataset.val) : cellA.textContent.trim().toLowerCase();
            const valB = cellB.dataset.val !== undefined ? parseFloat(cellB.dataset.val) : cellB.textContent.trim().toLowerCase();

            if (valA < valB) return sortState.dir === 'asc' ? -1 : 1;
            if (valA > valB) return sortState.dir === 'asc' ?  1 : -1;
            return 0;
        });

        rows.forEach(row => tbody.appendChild(row));
    });
});
</script>
</body>
</html>
