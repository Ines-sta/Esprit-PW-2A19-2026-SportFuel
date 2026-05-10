<?php
/**
 * BackOffice — Liste des repas
 */
require_once __DIR__ . '/../../Controller/core/RepasController.php';
require_once __DIR__ . '/../../Controller/core/PlanAlimentaireController.php';
$repasController = new RepasController();
$planController = new PlanAlimentaireController();
$plans = $planController->listPlans();
$aliments = $repasController->getSelectableAliments();

$id_plan = $_GET['id_plan'] ?? null;
if ($id_plan) {
    $repasList = $repasController->listRepasByPlan($id_plan);
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
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel/public/css/style.css">
</head>
<body>
    <?php
    $sidebarActive = 'plans';
    include __DIR__ . '/../partials/backoffice_sidebar.php';
    ?>

    <div class="main-area">
        <div class="page-header">
            <h1>Repas<?= $planFiltre ? ' — ' . htmlspecialchars($planFiltre->getNom()) : '' ?></h1>
            <div class="page-date"><?= count($repasList) ?> repas enregistre(s)</div>
        </div>

        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    <h3>Liste des repas</h3>
                    <div style="display:flex;gap:10px;">
                        <?php if ($planFiltre): ?>
                            <a href="index.php?page=back&action=listRepas" class="btn btn-outline btn-sm">Tous les repas</a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-accent btn-sm" id="openAddRepasModalBtn">+ Nouveau repas</button>
                    </div>
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
                                <th class="sortable" data-col="4">Aliments <span class="sort-icon">&#8597;</span></th>
                                <th class="sortable" data-col="5">Kcal <span class="sort-icon">&#8597;</span></th>
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
                                    <td data-val="<?= (int)($repas['aliments_count'] ?? 0) ?>">
                                        <span class="badge"><?= (int)($repas['aliments_count'] ?? 0) ?> aliment(s)</span>
                                    </td>
                                    <td data-val="<?= $repas['kcal'] ?>">
                                        <span class="badge badge-actif"><?= htmlspecialchars($repas['kcal']) ?> kcal</span>
                                    </td>
                                    <td>
                                        <div class="td-actions">
                                            <button type="button"
                                                    class="btn btn-outline btn-sm openUpdateRepasModalBtn"
                                                    data-id="<?= (int)$repas['id_repas'] ?>"
                                                    data-id-plan="<?= (int)$repas['id_plan'] ?>"
                                                    data-jour="<?= htmlspecialchars($repas['jour'], ENT_QUOTES) ?>"
                                                    data-type="<?= htmlspecialchars($repas['type_repas'], ENT_QUOTES) ?>"
                                                    data-description="<?= htmlspecialchars($repas['description'], ENT_QUOTES) ?>"
                                                        data-kcal="<?= (int)$repas['kcal'] ?>"
                                                        data-items="<?= htmlspecialchars(json_encode($repasController->getRepasAliments((int)$repas['id_repas']), JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>">Modifier</button>
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

        <div id="addRepasModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="addRepasModalTitle">
            <div class="modal">
                <div class="form-card-header" style="padding:0 0 12px; border-bottom:1px solid var(--border); margin-bottom:12px;">
                    <h3 id="addRepasModalTitle" style="margin:0;">Nouveau repas</h3>
                    <button type="button" class="btn btn-outline btn-sm" id="closeAddRepasModalBtn">Fermer</button>
                </div>
                <form id="addRepasForm" method="POST" action="index.php?page=back&action=addRepas" novalidate>
                    <div class="form-group">
                        <label for="add_id_plan">Plan alimentaire</label>
                        <select id="add_id_plan" name="id_plan" required>
                            <option value="">-- Selectionner un plan --</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?= (int)$plan->getIdPlan() ?>" <?= ($planFiltre && (int)$planFiltre->getIdPlan() === (int)$plan->getIdPlan()) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($plan->getNom()) ?> (S<?= (int)$plan->getSemaine() ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="add_jour">Jour</label>
                            <select id="add_jour" name="jour" required>
                                <option value="">-- Jour --</option>
                                <?php foreach (['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'] as $j): ?>
                                    <option value="<?= $j ?>"><?= $j ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="add_type_repas">Type de repas</label>
                            <select id="add_type_repas" name="type_repas" required>
                                <option value="">-- Type --</option>
                                <option value="petit_dejeuner">Petit-dejeuner</option>
                                <option value="dejeuner">Dejeuner</option>
                                <option value="diner">Diner</option>
                                <option value="collation">Collation</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Aliments du repas</label>
                        <div id="add_items_container" class="repas-items"></div>
                        <button type="button" class="btn btn-outline btn-sm" id="addRepasItemRowBtn">+ Ajouter un aliment</button>
                    </div>

                    <div class="form-group">
                        <label for="add_description">Description</label>
                        <textarea id="add_description" name="description" required minlength="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="add_kcal">Calories (kcal)</label>
                        <input type="number" id="add_kcal" name="kcal" required min="1" max="10000">
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-outline" id="cancelAddRepasBtn">Annuler</button>
                        <button type="submit" class="btn btn-accent">Enregistrer le repas</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editRepasModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="editRepasModalTitle">
            <div class="modal">
                <div class="form-card-header" style="padding:0 0 12px; border-bottom:1px solid var(--border); margin-bottom:12px;">
                    <h3 id="editRepasModalTitle" style="margin:0;">Modifier le repas</h3>
                    <button type="button" class="btn btn-outline btn-sm" id="closeEditRepasModalBtn">Fermer</button>
                </div>
                <form id="editRepasForm" method="POST" action="#" novalidate>
                    <div class="form-group">
                        <label for="edit_id_plan">Plan alimentaire</label>
                        <select id="edit_id_plan" name="id_plan" required>
                            <option value="">-- Selectionner un plan --</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?= (int)$plan->getIdPlan() ?>">
                                    <?= htmlspecialchars($plan->getNom()) ?> (S<?= (int)$plan->getSemaine() ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_jour">Jour</label>
                            <select id="edit_jour" name="jour" required>
                                <option value="">-- Jour --</option>
                                <?php foreach (['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'] as $j): ?>
                                    <option value="<?= $j ?>"><?= $j ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_type_repas">Type de repas</label>
                            <select id="edit_type_repas" name="type_repas" required>
                                <option value="">-- Type --</option>
                                <option value="petit_dejeuner">Petit-dejeuner</option>
                                <option value="dejeuner">Dejeuner</option>
                                <option value="diner">Diner</option>
                                <option value="collation">Collation</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Aliments du repas</label>
                        <div id="edit_items_container" class="repas-items"></div>
                        <button type="button" class="btn btn-outline btn-sm" id="editRepasItemRowBtn">+ Ajouter un aliment</button>
                    </div>

                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description" required minlength="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_kcal">Calories (kcal)</label>
                        <input type="number" id="edit_kcal" name="kcal" required min="1" max="10000">
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-outline" id="cancelEditRepasBtn">Annuler</button>
                        <button type="submit" class="btn btn-accent">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>

<script>
const addRepasModal = document.getElementById('addRepasModal');
const editRepasModal = document.getElementById('editRepasModal');
const addRepasForm = document.getElementById('addRepasForm');
const editRepasForm = document.getElementById('editRepasForm');
const alimentsCatalog = <?= json_encode(array_map(static function ($aliment) {
    return [
        'id_aliment' => (int)$aliment['id_aliment'],
        'nom' => (string)$aliment['nom'],
        'kcal_portion' => (float)$aliment['kcal_portion'],
    ];
}, $aliments), JSON_UNESCAPED_UNICODE) ?>;

function formatNumber(value) {
    const numericValue = Number(value);
    if (!Number.isFinite(numericValue)) {
        return '0';
    }
    return Number.isInteger(numericValue)
        ? String(numericValue)
        : String(Math.round(numericValue * 100) / 100);
}

function unitToPortions(quantity, unit) {
    const q = Number(quantity);
    if (!Number.isFinite(q) || q <= 0) {
        return 0;
    }
    if (unit === 'g' || unit === 'ml') {
        return q / 100;
    }
    if (unit === 'kg' || unit === 'L') {
        return q * 10;
    }
    return q;
}

function findAlimentById(idAliment) {
    return alimentsCatalog.find(item => Number(item.id_aliment) === Number(idAliment)) || null;
}

function createRepasItemRow(formPrefix, initialItem = {}) {
    const row = document.createElement('div');
    row.className = 'repas-item-row';

    const selectedAliment = Number(initialItem.id_aliment || 0);
    const selectedUnit = initialItem.unite || 'g';
    const selectedQuantity = initialItem.quantite || '';

    const alimentOptions = ['<option value="">-- Aliment --</option>']
        .concat(alimentsCatalog.map(aliment => {
            const selected = Number(aliment.id_aliment) === selectedAliment ? ' selected' : '';
            return `<option value="${aliment.id_aliment}"${selected}>${aliment.nom}</option>`;
        }))
        .join('');

    const unitOptions = ['g', 'kg', 'ml', 'L', 'piece']
        .map(unit => `<option value="${unit}"${unit === selectedUnit ? ' selected' : ''}>${unit}</option>`)
        .join('');

    row.innerHTML = `
        <select name="aliments[]" class="repas-item-aliment">${alimentOptions}</select>
        <input type="number" name="quantites[]" class="repas-item-quantity" min="0.1" step="0.1" value="${selectedQuantity}">
        <select name="unites[]" class="repas-item-unit">${unitOptions}</select>
        <button type="button" class="btn btn-danger btn-sm repas-item-remove">Retirer</button>
    `;

    row.querySelector('.repas-item-remove').addEventListener('click', () => {
        const container = row.parentElement;
        row.remove();
        if (container && !container.querySelector('.repas-item-row')) {
            addRepasItemRow(formPrefix);
        }
        recalculateRepasSummary(formPrefix);
    });

    row.querySelectorAll('select, input').forEach(field => {
        field.addEventListener('change', () => recalculateRepasSummary(formPrefix));
        field.addEventListener('input', () => recalculateRepasSummary(formPrefix));
    });

    return row;
}

function addRepasItemRow(formPrefix, initialItem = {}) {
    const container = document.getElementById(`${formPrefix}_items_container`);
    if (!container) {
        return;
    }
    container.appendChild(createRepasItemRow(formPrefix, initialItem));
}

function setRepasItems(formPrefix, items) {
    const container = document.getElementById(`${formPrefix}_items_container`);
    if (!container) {
        return;
    }
    container.innerHTML = '';

    if (Array.isArray(items) && items.length > 0) {
        items.forEach(item => addRepasItemRow(formPrefix, item));
    } else {
        addRepasItemRow(formPrefix);
    }

    recalculateRepasSummary(formPrefix);
}

function collectValidRepasItems(formPrefix) {
    const container = document.getElementById(`${formPrefix}_items_container`);
    if (!container) {
        return [];
    }

    return Array.from(container.querySelectorAll('.repas-item-row')).map(row => {
        const idAliment = Number(row.querySelector('.repas-item-aliment').value || 0);
        const quantite = Number(row.querySelector('.repas-item-quantity').value || 0);
        const unite = row.querySelector('.repas-item-unit').value || 'g';
        return { id_aliment: idAliment, quantite, unite };
    }).filter(item => item.id_aliment > 0 && item.quantite > 0);
}

function recalculateRepasSummary(formPrefix) {
    const descriptionEl = document.getElementById(`${formPrefix}_description`);
    const kcalEl = document.getElementById(`${formPrefix}_kcal`);
    const items = collectValidRepasItems(formPrefix);

    if (!descriptionEl || !kcalEl) {
        return;
    }

    if (items.length === 0) {
        return;
    }

    const descriptionParts = [];
    let totalKcal = 0;

    items.forEach(item => {
        const aliment = findAlimentById(item.id_aliment);
        if (!aliment) {
            return;
        }
        const portions = unitToPortions(item.quantite, item.unite);
        totalKcal += portions * Number(aliment.kcal_portion || 0);
        descriptionParts.push(`${aliment.nom} (${formatNumber(item.quantite)} ${item.unite})`);
    });

    if (descriptionParts.length > 0) {
        descriptionEl.value = descriptionParts.join(', ');
        kcalEl.value = String(Math.round(totalKcal));
    }
}

function validateRepasForm(formPrefix) {
    const idPlan = document.getElementById(`${formPrefix}_id_plan`).value;
    const jour = document.getElementById(`${formPrefix}_jour`).value;
    const typeRepas = document.getElementById(`${formPrefix}_type_repas`).value;
    const description = document.getElementById(`${formPrefix}_description`).value.trim();
    const kcal = Number(document.getElementById(`${formPrefix}_kcal`).value || 0);
    const items = collectValidRepasItems(formPrefix);

    if (!idPlan || !jour || !typeRepas) {
        return false;
    }

    if (items.length > 0) {
        recalculateRepasSummary(formPrefix);
        return true;
    }

    return description.length >= 3 && kcal > 0;
}

function openAddRepasModal() {
    addRepasForm.reset();
    setRepasItems('add', []);
    addRepasModal.classList.add('active');
}

function closeAddRepasModal() {
    addRepasModal.classList.remove('active');
}

function openEditRepasModal(btn) {
    editRepasForm.action = `index.php?page=back&action=updateRepas&id=${btn.dataset.id}`;
    document.getElementById('edit_id_plan').value = btn.dataset.idPlan || '';
    document.getElementById('edit_jour').value = btn.dataset.jour || '';
    document.getElementById('edit_type_repas').value = btn.dataset.type || '';
    document.getElementById('edit_description').value = btn.dataset.description || '';
    document.getElementById('edit_kcal').value = btn.dataset.kcal || '';

    let items = [];
    if (btn.dataset.items) {
        try {
            items = JSON.parse(btn.dataset.items);
        } catch (error) {
            items = [];
        }
    }
    setRepasItems('edit', items);

    editRepasModal.classList.add('active');
}

function closeEditRepasModal() {
    editRepasModal.classList.remove('active');
}

document.getElementById('openAddRepasModalBtn').addEventListener('click', openAddRepasModal);
document.getElementById('closeAddRepasModalBtn').addEventListener('click', closeAddRepasModal);
document.getElementById('cancelAddRepasBtn').addEventListener('click', closeAddRepasModal);
document.getElementById('addRepasItemRowBtn').addEventListener('click', function () {
    addRepasItemRow('add');
});
addRepasModal.addEventListener('click', function (e) {
    if (e.target === addRepasModal) closeAddRepasModal();
});

document.getElementById('closeEditRepasModalBtn').addEventListener('click', closeEditRepasModal);
document.getElementById('cancelEditRepasBtn').addEventListener('click', closeEditRepasModal);
document.getElementById('editRepasItemRowBtn').addEventListener('click', function () {
    addRepasItemRow('edit');
});
editRepasModal.addEventListener('click', function (e) {
    if (e.target === editRepasModal) closeEditRepasModal();
});

document.querySelectorAll('.openUpdateRepasModalBtn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        openEditRepasModal(btn);
    });
});

addRepasForm.addEventListener('submit', function (e) {
    if (!validateRepasForm('add')) {
        e.preventDefault();
        alert('Veuillez remplir les champs obligatoires ou selectionner au moins un aliment valide.');
    }
});

editRepasForm.addEventListener('submit', function (e) {
    if (!validateRepasForm('edit')) {
        e.preventDefault();
        alert('Veuillez remplir les champs obligatoires ou selectionner au moins un aliment valide.');
    }
});

setRepasItems('add', []);
setRepasItems('edit', []);

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
