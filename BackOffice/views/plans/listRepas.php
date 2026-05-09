<?php
/**
 * BackOffice — Liste des repas
 */
require_once 'Controller/RepasController.php';
$repasController = new RepasController();

$id_plan = $_GET['id_plan'] ?? null;
if ($id_plan) {
    $repasList = $repasController->listRepasByPlan($id_plan);
    require_once 'Controller/PlanAlimentaireController.php';
    $planController = new PlanAlimentaireController();
    $planFiltre = $planController->getPlan($id_plan);
} else {
    $repasList = $repasController->listRepas();
    $planFiltre = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repas — SportFuel Admin</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'View/partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>Repas<?= $planFiltre ? ' — ' . htmlspecialchars($planFiltre->getNom()) : '' ?></h1>
                <div class="page-date"><?= count($repasList) ?> repas enregistre(s)</div>
            </div>
            <div style="display:flex;gap:10px;">
                <?php if ($planFiltre): ?>
                    <a href="index.php?page=back&action=listRepas" class="btn btn-outline btn-sm">Tous les repas</a>
                <?php endif; ?>
                <a href="index.php?page=back&action=addRepas" class="btn btn-accent">+ Nouveau repas</a>
            </div>
        </div>

        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    <h3>Liste des repas</h3>
                </div>
                <div class="search-wrap">
                    <input type="text" id="searchInput" class="search-input" placeholder="Rechercher...">
                </div>
                <?php if (empty($repasList)): ?>
                    <div class="empty-state">Aucun repas enregistre.</div>
                <?php else: ?>
                    <table class="data-table" id="repasTable">
                        <thead>
                            <tr>
                                <th class="sortable" data-col="0">Plan <span class="sort-icon">&#8597;</span></th>
                                <th class="sortable" data-col="1">Jour <span class="sort-icon">&#8597;</span></th>
                                <th class="sortable" data-col="2">Type <span class="sort-icon">&#8597;</span></th>
                                <th>Description</th>
                                <th class="sortable" data-col="4">Kcal <span class="sort-icon">&#8597;</span></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $jourOrder = ['Lundi'=>1,'Mardi'=>2,'Mercredi'=>3,'Jeudi'=>4,'Vendredi'=>5,'Samedi'=>6,'Dimanche'=>7];
                            foreach ($repasList as $repas): ?>
                                <tr data-search="<?= strtolower(htmlspecialchars($repas['plan_nom'] ?? '')) . ' ' . strtolower(htmlspecialchars($repas['description'])) ?>">
                                    <td><?= htmlspecialchars($repas['plan_nom'] ?? '—') ?></td>
                                    <td data-val="<?= $jourOrder[$repas['jour']] ?? 0 ?>"><?= htmlspecialchars($repas['jour']) ?></td>
                                    <td><?= str_replace('_', ' ', htmlspecialchars($repas['type_repas'])) ?></td>
                                    <td class="td-muted"><?= htmlspecialchars(mb_strimwidth($repas['description'], 0, 60, '...')) ?></td>
                                    <td data-val="<?= $repas['kcal'] ?>">
                                        <span class="badge badge-actif"><?= htmlspecialchars($repas['kcal']) ?> kcal</span>
                                    </td>
                                    <td>
                                        <div class="td-actions">
                                            <a href="index.php?page=back&action=updateRepas&id=<?= $repas['id_repas'] ?>" class="btn btn-outline btn-sm">Modifier</a>
                                            <a href="index.php?page=back&action=deleteRepas&id=<?= $repas['id_repas'] ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Supprimer ce repas ?')">Supprimer</a>
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
    document.querySelectorAll('#repasTable tbody tr').forEach(row => {
        row.style.display = row.dataset.search.includes(q) ? '' : 'none';
    });
});

// ── SORT ─────────────────────────────────────────
let sortState = { col: null, dir: 'asc' };

document.querySelectorAll('.sortable').forEach(th => {
    th.addEventListener('click', function() {
        const col = parseInt(this.dataset.col);
        sortState.dir = sortState.col === col && sortState.dir === 'asc' ? 'desc' : 'asc';
        sortState.col = col;

        document.querySelectorAll('.sortable .sort-icon').forEach(ic => ic.textContent = '⇅');
        this.querySelector('.sort-icon').textContent = sortState.dir === 'asc' ? '↑' : '↓';

        const tbody = document.querySelector('#repasTable tbody');
        const rows  = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const cellA = a.cells[col];
            const cellB = b.cells[col];
            const valA  = cellA.dataset.val !== undefined ? parseFloat(cellA.dataset.val) : cellA.textContent.trim().toLowerCase();
            const valB  = cellB.dataset.val !== undefined ? parseFloat(cellB.dataset.val) : cellB.textContent.trim().toLowerCase();
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
