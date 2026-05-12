<?php
/**
 * BackOffice — Liste des plans alimentaires
 */
require_once __DIR__ . '/../../Controller/core/PlanAlimentaireController.php';
$planController = new PlanAlimentaireController();
$plans = $planController->listPlans();
$utilisateurs = $planController->getSelectableUtilisateursForCurrentRole();

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
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel-main/public/css/style.css">
</head>
<body>
    <?php
    $sidebarActive = 'plans';
    include __DIR__ . '/../partials/backoffice_sidebar.php';
    ?>

    <div class="main-area">
        <div class="page-header">
            <h1>Vue d'ensemble</h1>
            <div class="page-date"><?= $today ?></div>
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
                    require_once __DIR__ . '/../../Controller/core/RepasController.php';
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
                    <button type="button" id="openAddPlanModalBtn" class="btn btn-accent btn-sm">+ Nouveau plan</button>
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
                                            <button type="button"
                                                    class="btn btn-outline btn-sm openUpdatePlanModalBtn"
                                                    data-id="<?= (int)$plan->getIdPlan() ?>"
                                                    data-id-utilisateur="<?= (int)$plan->getIdUtilisateur() ?>"
                                                    data-nom="<?= htmlspecialchars($plan->getNom(), ENT_QUOTES) ?>"
                                                    data-type="<?= htmlspecialchars($plan->getType(), ENT_QUOTES) ?>"
                                                    data-kcal="<?= (int)$plan->getKcalCibles() ?>"
                                                    data-semaine="<?= (int)$plan->getSemaine() ?>"
                                                    data-date-debut="<?= htmlspecialchars($plan->getDateDebut(), ENT_QUOTES) ?>"
                                                    data-date-fin="<?= htmlspecialchars($plan->getDateFin(), ENT_QUOTES) ?>">Modifier</button>
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

        <div id="addPlanModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="addPlanModalTitle">
            <div class="modal">
                <div class="form-card-header" style="padding:0 0 12px; border-bottom:1px solid var(--border); margin-bottom:12px;">
                    <h3 id="addPlanModalTitle" style="margin:0;">Nouveau plan alimentaire</h3>
                    <button type="button" class="btn btn-outline btn-sm" id="closeAddPlanModalBtn">Fermer</button>
                </div>
                <form id="addPlanForm" method="POST" action="index.php?page=back&action=addPlan" novalidate>
                    <div class="form-group">
                        <label for="nom">Nom du plan</label>
                        <input type="text" id="nom" name="nom" placeholder="Ex: Plan musculation semaine 1" required>
                        <div class="field-msg" id="msg-nom"></div>
                    </div>

                    <div class="form-group">
                        <label for="id_utilisateur">Sportif</label>
                        <select id="id_utilisateur" name="id_utilisateur" required>
                            <option value="">-- Selectionner un sportif --</option>
                            <?php foreach ($utilisateurs as $utilisateur): ?>
                                <option value="<?= (int)$utilisateur['id'] ?>"><?= htmlspecialchars($utilisateur['nom']) ?> (#<?= (int)$utilisateur['id'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="field-msg" id="msg-id_utilisateur"></div>
                    </div>

                    <div class="form-group">
                        <label for="type">Type de plan</label>
                        <select id="type" name="type" required>
                            <option value="">-- Selectionner un type --</option>
                            <option value="prise_de_masse">Prise de masse</option>
                            <option value="perte_de_poids">Perte de poids</option>
                            <option value="maintien">Maintien</option>
                            <option value="endurance">Endurance</option>
                        </select>
                        <div class="field-msg" id="msg-type"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="kcal_cibles">Calories cibles (kcal)</label>
                            <input type="number" id="kcal_cibles" name="kcal_cibles" placeholder="Ex: 2500" required min="1000" max="6000">
                            <div class="field-msg" id="msg-kcal_cibles"></div>
                        </div>
                        <div class="form-group">
                            <label for="semaine">Semaine</label>
                            <input type="number" id="semaine" name="semaine" placeholder="1 - 52" required min="1" max="52">
                            <div class="field-msg" id="msg-semaine"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_debut">Date de debut</label>
                            <input type="date" id="date_debut" name="date_debut" required>
                            <div class="field-msg" id="msg-date_debut"></div>
                        </div>
                        <div class="form-group">
                            <label for="date_fin">Date de fin</label>
                            <input type="date" id="date_fin" name="date_fin" required>
                            <div class="field-msg" id="msg-date_fin"></div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-outline" id="cancelAddPlanBtn">Annuler</button>
                        <button type="submit" id="submitBtn" class="btn btn-accent">Enregistrer le plan</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editPlanModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="editPlanModalTitle">
            <div class="modal">
                <div class="form-card-header" style="padding:0 0 12px; border-bottom:1px solid var(--border); margin-bottom:12px;">
                    <h3 id="editPlanModalTitle" style="margin:0;">Modifier le plan alimentaire</h3>
                    <button type="button" class="btn btn-outline btn-sm" id="closeEditPlanModalBtn">Fermer</button>
                </div>
                <form id="editPlanForm" method="POST" action="#" novalidate>
                    <input type="hidden" id="edit_id_utilisateur" name="id_utilisateur">

                    <div class="form-group">
                        <label for="edit_nom">Nom du plan</label>
                        <input type="text" id="edit_nom" name="nom" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_type">Type de plan</label>
                        <select id="edit_type" name="type" required>
                            <option value="prise_de_masse">Prise de masse</option>
                            <option value="perte_de_poids">Perte de poids</option>
                            <option value="maintien">Maintien</option>
                            <option value="endurance">Endurance</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_kcal_cibles">Calories cibles (kcal)</label>
                            <input type="number" id="edit_kcal_cibles" name="kcal_cibles" required min="1000" max="6000">
                        </div>
                        <div class="form-group">
                            <label for="edit_semaine">Semaine</label>
                            <input type="number" id="edit_semaine" name="semaine" required min="1" max="52">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_date_debut">Date de debut</label>
                            <input type="date" id="edit_date_debut" name="date_debut" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_date_fin">Date de fin</label>
                            <input type="date" id="edit_date_fin" name="date_fin" required>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-outline" id="cancelEditPlanBtn">Annuler</button>
                        <button type="submit" class="btn btn-accent">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>

<script>
const addPlanModal = document.getElementById('addPlanModal');
const openAddPlanModalBtn = document.getElementById('openAddPlanModalBtn');
const closeAddPlanModalBtn = document.getElementById('closeAddPlanModalBtn');
const cancelAddPlanBtn = document.getElementById('cancelAddPlanBtn');
const editPlanModal = document.getElementById('editPlanModal');
const closeEditPlanModalBtn = document.getElementById('closeEditPlanModalBtn');
const cancelEditPlanBtn = document.getElementById('cancelEditPlanBtn');
const editPlanForm = document.getElementById('editPlanForm');

function openAddPlanModal() {
    addPlanModal.classList.add('active');
}

function closeAddPlanModal() {
    addPlanModal.classList.remove('active');
}

function openEditPlanModal(button) {
    editPlanForm.action = `index.php?page=back&action=updatePlan&id=${button.dataset.id}`;
    document.getElementById('edit_id_utilisateur').value = button.dataset.idUtilisateur || '';
    document.getElementById('edit_nom').value = button.dataset.nom || '';
    document.getElementById('edit_type').value = button.dataset.type || 'maintien';
    document.getElementById('edit_kcal_cibles').value = button.dataset.kcal || '';
    document.getElementById('edit_semaine').value = button.dataset.semaine || '';
    document.getElementById('edit_date_debut').value = button.dataset.dateDebut || '';
    document.getElementById('edit_date_fin').value = button.dataset.dateFin || '';
    editPlanModal.classList.add('active');
}

function closeEditPlanModal() {
    editPlanModal.classList.remove('active');
}

openAddPlanModalBtn.addEventListener('click', openAddPlanModal);
closeAddPlanModalBtn.addEventListener('click', closeAddPlanModal);
cancelAddPlanBtn.addEventListener('click', closeAddPlanModal);
addPlanModal.addEventListener('click', function (e) {
    if (e.target === addPlanModal) {
        closeAddPlanModal();
    }
});
closeEditPlanModalBtn.addEventListener('click', closeEditPlanModal);
cancelEditPlanBtn.addEventListener('click', closeEditPlanModal);
editPlanModal.addEventListener('click', function (e) {
    if (e.target === editPlanModal) {
        closeEditPlanModal();
    }
});

document.querySelectorAll('.openUpdatePlanModalBtn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        openEditPlanModal(btn);
    });
});

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
<script src="public/js/addPlan.js"></script>
</body>
</html>
