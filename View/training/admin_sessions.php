<?php
/**
 * BackOffice — Gestion des entraînements (Séances)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Controller/core/role_context.php';
if (!sportfuel_is_backoffice_role()) {
    http_response_code(403);
    echo 'Acces refuse: role BackOffice requis.';
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportFuel Admin — Gestion des séances</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel/public/css/style.css">
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel/public/css/entrainement.css">
    <style>
        .admin-session-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 1200;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .admin-session-overlay.active {
            display: flex;
        }

        .admin-session-modal {
            width: min(760px, 92vw);
            max-height: 88vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 14px;
            border: 1px solid var(--border, #e4e9e4);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.2);
            padding: 20px;
        }

        .admin-session-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <?php
    $sidebarActive = 'training';
    include __DIR__ . '/../partials/backoffice_sidebar.php';
    ?>

    <div class="main-area">
            <div class="topbar">
                <h1>⚙️ Gestion des séances</h1>
                <span class="date"><?php echo date('l j F Y', strtotime(date('Y-m-d'))); ?></span>
            </div>

            <div class="training-content">

            <!-- Cartes stats -->
            <div class="stat-cards section-spacing">
                <div class="stat-card"><div class="stat-value" id="stat-total">—</div><div class="stat-label">Séances totales</div></div>
                <div class="stat-card"><div class="stat-value" id="stat-attente">—</div><div class="stat-label">En attente</div></div>
                <div class="stat-card"><div class="stat-value" id="stat-validee">—</div><div class="stat-label">Validées</div></div>
                <div class="stat-card"><div class="stat-value" id="stat-types">—</div><div class="stat-label">Types distincts</div></div>
            </div>

            <!-- Navigation buttons -->
            <div class="search-bar training-actions section-spacing">
                <a href="/Esprit-PW-2A19-2026-SportFuel/index.php?page=training" class="btn btn-outline">🏋️ Gérer les programmes</a>
            </div>

            <!-- Filtres -->
            <div class="search-bar training-filters section-spacing">
                <select id="filter-type"><option value="Tous">Tous les types</option></select>
                <select id="filter-statut"><option value="Tous">Tous les statuts</option><option value="Validé">Validé</option><option value="En attente">En attente</option><option value="Inactif">Inactif</option></select>
                <input type="text" id="filter-search" placeholder="Rechercher une seance..." autocomplete="off">
                <button class="btn btn-primary btn-filter">Filtrer</button>
                <button class="btn btn-outline btn-reset">Reset</button>
            </div>

            <!-- Tableau des séances -->
            <div class="card training-panel section-spacing">
                <div class="training-panel-head">
                    <h2 class="training-panel-title">Liste des séances</h2>
                    <span class="training-panel-note">Sélectionnez une séance pour gérer ses exercices.</span>
                </div>
                <div class="table-wrapper" style="margin-bottom:0; border:none; border-radius:0;">
                    <table>
                        <thead>

                <!-- Barre d'actions -->
                <div class="export-bar section-spacing">
                    <button class="btn btn-outline" type="button" id="exportCsvBtn">Exporter CSV</button>
                    <button class="btn btn-outline" type="button" id="printBtn">Imprimer</button>
                </div>
                            <tr><th>Nom</th><th>Sport</th><th>Date début</th><th>Niveau</th><th>Fréquence</th><th>Planifiées</th><th>Progression</th><th>Coach</th><th>Statut</th><th>Actions</th></tr>
                        </thead>
                        <tbody id="seances-tbody">
                            <tr><td colspan="10" style="text-align:center;">Chargement...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card training-panel section-spacing exercise-card">
                <div class="training-panel-head">
                    <h2 class="training-panel-title">Exercices de la séance</h2>
                </div>
                <div class="training-panel-body">
                    <div class="exercise-head">
                        <div class="form-group">
                            <label>Séance / programme cible</label>
                            <select id="exerciseProgramSelect">
                                <option value="">Sélectionnez un programme</option>
                            </select>
                        </div>
                        <div class="exercise-actions">
                            <button type="button" class="btn btn-accent" id="openExerciseModalBtn">➕ Ajouter un exercice</button>
                            <button type="button" class="btn btn-secondary" id="refreshExercisesBtn">🔄 Rafraîchir</button>
                        </div>
                    </div>

                    <table class="exercise-table">
                        <thead>
                            <tr><th>ID</th><th>Nom</th><th>Durée</th><th>Séries</th><th>Répétitions</th><th>Charge</th><th>Distance</th><th>Actions</th></tr>
                        </thead>
                        <tbody id="exerciseTableBody">
                            <tr><td colspan="8" style="text-align:center;">Sélectionnez une séance.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            </div>
    </div>

<div id="exerciseModalBackdrop" class="admin-session-overlay" role="dialog" aria-modal="true" aria-labelledby="exerciseModalTitle">
    <div class="admin-session-modal">
        <div class="admin-session-header">
            <h3 id="exerciseModalTitle" style="margin:0;">➕ Ajouter un exercice à la séance</h3>
            <button type="button" class="btn btn-secondary" onclick="closeExerciseModal()">Fermer</button>
        </div>
        <form id="exerciseForm" novalidate>
            <div id="exerciseFormErrors" role="alert" style="display:none; margin-bottom:16px; padding:12px 16px; background:#fde8e8; border:1px solid #f5c2c7; border-radius:8px; color:#842029; font-size:14px;"></div>
            <div class="form-row">
                <div class="form-group"><label>Nom exercice *</label><input type="text" id="ex_nom" maxlength="120" required placeholder="Ex: Squat"></div>
                <div class="form-group"><label>Durée (secondes) *</label><input type="number" id="ex_duree" min="1" required placeholder="Ex: 60"></div>
                <div class="form-group"><label>Séries</label><input type="number" id="ex_series" min="1" placeholder="Ex: 4"></div>
                <div class="form-group"><label>Répétitions</label><input type="number" id="ex_repetitions" min="1" placeholder="Ex: 12"></div>
                <div class="form-group"><label>Charge (kg)</label><input type="number" id="ex_charge" min="0" step="0.1" placeholder="Ex: 40"></div>
                <div class="form-group"><label>Distance (km)</label><input type="number" id="ex_distance" min="0" step="0.1" placeholder="Ex: 5"></div>
            </div>
            <button type="submit" class="btn btn-primary">📌 Enregistrer l'exercice</button>
        </form>
    </div>
</div>

<script src="/Esprit-PW-2A19-2026-SportFuel/public/js/api.js"></script>
<script src="/Esprit-PW-2A19-2026-SportFuel/public/js/validation.js"></script>
<script>
    const currentRole = '<?php echo htmlspecialchars(sportfuel_current_role()); ?>';
    let allSeances = [];
    let filteredSeances = [];
    let currentProgramId = null;

    function getPlannedSessions(meta) {
        const freq = parseInt(meta.frequence, 10);
        const weeks = parseInt(meta.duree_semaines, 10);
        if (Number.isNaN(freq) || Number.isNaN(weeks) || freq < 1 || weeks < 1) return null;
        return freq * weeks;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatDateFr(dateRaw) {
        if (!dateRaw) return '—';
        const d = new Date(`${dateRaw}T00:00:00`);
        if (Number.isNaN(d.getTime())) return '—';
        return d.toLocaleDateString('fr-FR');
    }

    function updateStats(rows) {
        const total = rows.length;
        const attente = rows.filter((r) => String(r.statut || '').toLowerCase().includes('attente')).length;
        const validees = rows.filter((r) => String(r.statut || '').toLowerCase().includes('valid')).length;
        const types = new Set(rows.map((r) => String(r.titre || '').trim().toLowerCase()).filter(Boolean)).size;

        document.getElementById('stat-total').textContent = String(total);
        document.getElementById('stat-attente').textContent = String(attente);
        document.getElementById('stat-validee').textContent = String(validees);
        document.getElementById('stat-types').textContent = String(types);
    }

    function populateTypeFilter(rows) {
        const select = document.getElementById('filter-type');
        const current = select.value;
        const types = Array.from(new Set(rows.map((r) => String(r.titre || '').trim()).filter(Boolean))).sort();
        select.innerHTML = '<option>Tous</option>' + types.map((t) => `<option>${escapeHtml(t)}</option>`).join('');
        if (types.includes(current)) {
            select.value = current;
        }
    }

    function renderSeances(rows) {
        const tbody = document.getElementById('seances-tbody');
        const select = document.getElementById('exerciseProgramSelect');

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">Aucune séance trouvée.</td></tr>';
            select.innerHTML = '<option value="">Sélectionnez un programme</option>';
            return;
        }

        tbody.innerHTML = rows.map((p) => {
            const planned = getPlannedSessions(p);
            const state = String(p.statut || 'Inactif').trim();
            const statusKey = state.toLowerCase();
            const progress = statusKey.includes('valid') ? 100 : (statusKey.includes('attente') ? 40 : 15);
            return `
                <tr>
                    <td>${escapeHtml(p.libelle_programme || 'Programme #' + p.id_entrainement)}</td>
                    <td>${escapeHtml(p.titre || '—')}</td>
                    <td>${formatDateFr(p.date_entrainement)}</td>
                    <td>${escapeHtml(p.niveau || '—')}</td>
                    <td>${escapeHtml(p.frequence || '—')}</td>
                    <td>${planned === null ? '—' : planned}</td>
                    <td>${progress}%</td>
                    <td>${escapeHtml(p.coach || '—')}</td>
                    <td><span class="badge ${statusKey.includes('valid') ? 'badge-actif' : (statusKey.includes('attente') ? 'badge-en-attente' : 'badge-inactif')}">${escapeHtml(state)}</span></td>
                    <td><button class="btn btn-secondary" type="button" onclick="selectProgram(${Number(p.id_entrainement)})">Exercices</button></td>
                </tr>
            `;
        }).join('');

        select.innerHTML = '<option value="">Sélectionnez un programme</option>' + rows.map((p) => (
            `<option value="${Number(p.id_entrainement)}">${escapeHtml((p.libelle_programme || 'Programme #' + p.id_entrainement) + ' — ' + (p.titre || '—'))}</option>`
        )).join('');
    }

    function applyFilters() {
        const type = document.getElementById('filter-type').value;
        const status = document.getElementById('filter-statut').value;
        const search = document.getElementById('filter-search').value.trim().toLowerCase();

        filteredSeances = allSeances.filter((row) => {
            const okType = type === 'Tous' || String(row.titre || '') === type;
            const rowStatus = String(row.statut || '').trim();
            const okStatus = status === 'Tous' || rowStatus === status;
            const name = String(row.libelle_programme || '').toLowerCase();
            const sport = String(row.titre || '').toLowerCase();
            const okSearch = search === '' || name.includes(search) || sport.includes(search);
            return okType && okStatus && okSearch;
        });

        renderSeances(filteredSeances);
        updateStats(filteredSeances);
    }

    function resetFilters() {
        document.getElementById('filter-type').value = 'Tous';
        document.getElementById('filter-statut').value = 'Tous';
        document.getElementById('filter-search').value = '';
        filteredSeances = allSeances.slice();
        renderSeances(filteredSeances);
        updateStats(filteredSeances);
    }

    function exportCurrentRowsCsv() {
        const rows = filteredSeances.slice();
        if (!rows.length) {
            alert('Aucune donnee a exporter.');
            return;
        }
        const header = ['Nom', 'Sport', 'Date debut', 'Niveau', 'Frequence', 'Planifiees', 'Progression', 'Coach', 'Statut'];
        const lines = [header.join(';')].concat(rows.map((p) => {
            const planned = getPlannedSessions(p);
            const statusKey = String(p.statut || '').toLowerCase();
            const progress = statusKey.includes('valid') ? 100 : (statusKey.includes('attente') ? 40 : 15);
            return [
                String(p.libelle_programme || 'Programme #' + p.id_entrainement),
                String(p.titre || ''),
                String(p.date_entrainement || ''),
                String(p.niveau || ''),
                String(p.frequence || ''),
                planned === null ? '' : String(planned),
                String(progress) + '%',
                String(p.coach || ''),
                String(String(p.statut || '').trim() || 'Inactif')
            ].map((v) => '"' + String(v).replace(/"/g, '""') + '"').join(';');
        }));

        const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'seances.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    async function loadSeances() {
        try {
            const data = await getProgrammes();
            allSeances = Array.isArray(data) ? data : [];
            filteredSeances = allSeances.slice();
            populateTypeFilter(allSeances);
            renderSeances(filteredSeances);
            updateStats(filteredSeances);
        } catch (e) {
            document.getElementById('seances-tbody').innerHTML =
                '<tr><td colspan="10" style="text-align:center;">Erreur de chargement des séances.</td></tr>';
        }
    }

    async function loadExercises(programId) {
        const body = document.getElementById('exerciseTableBody');
        if (!programId) {
            body.innerHTML = '<tr><td colspan="8" style="text-align:center;">Sélectionnez une séance.</td></tr>';
            return;
        }
        body.innerHTML = '<tr><td colspan="8" style="text-align:center;">Chargement...</td></tr>';
        try {
            const rows = await listExercicesSeance(programId);
            if (!rows.length) {
                body.innerHTML = '<tr><td colspan="8" style="text-align:center;">Aucun exercice pour cette séance.</td></tr>';
                return;
            }
            body.innerHTML = rows.map((ex) => `
                <tr>
                    <td>${Number(ex.id_exercice_seance)}</td>
                    <td>${escapeHtml(ex.nom_exercice || '—')}</td>
                    <td>${ex.duree_secondes ? Number(ex.duree_secondes) + ' s' : '—'}</td>
                    <td>${ex.series ? Number(ex.series) : '—'}</td>
                    <td>${ex.repetitions ? Number(ex.repetitions) : '—'}</td>
                    <td>${ex.charge_kg ? Number(ex.charge_kg) + ' kg' : '—'}</td>
                    <td>${ex.distance_km ? Number(ex.distance_km) + ' km' : '—'}</td>
                    <td><button class="btn btn-secondary" type="button" onclick="removeExercise(${Number(ex.id_exercice_seance)})">Supprimer</button></td>
                </tr>
            `).join('');
        } catch (e) {
            body.innerHTML = '<tr><td colspan="8" style="text-align:center;">Erreur de chargement des exercices.</td></tr>';
        }
    }

    function selectProgram(programId) {
        currentProgramId = Number(programId);
        const select = document.getElementById('exerciseProgramSelect');
        select.value = String(programId);
        loadExercises(currentProgramId);
    }

    function openExerciseModal() {
        const box = document.getElementById('exerciseFormErrors');
        box.style.display = 'none';
        box.textContent = '';
        if (!currentProgramId) {
            box.textContent = 'Sélectionnez d\'abord une séance avant d\'ajouter un exercice.';
            box.style.display = 'block';
        }
        document.getElementById('exerciseModalBackdrop').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeExerciseModal() {
        document.getElementById('exerciseModalBackdrop').classList.remove('active');
        document.body.style.overflow = '';
    }

    async function submitExerciseForm(event) {
        event.preventDefault();
        const form = document.getElementById('exerciseForm');
        if (!currentProgramId) {
            const box = document.getElementById('exerciseFormErrors');
            box.textContent = 'Veuillez sélectionner une séance.';
            box.style.display = 'block';
            return;
        }
        if (!validerFormExercice(form)) {
            return;
        }

        const payload = {
            id_entrainement: currentProgramId,
            nom_exercice: document.getElementById('ex_nom').value.trim(),
            duree_secondes: document.getElementById('ex_duree').value,
            series: document.getElementById('ex_series').value,
            repetitions: document.getElementById('ex_repetitions').value,
            charge_kg: document.getElementById('ex_charge').value,
            distance_km: document.getElementById('ex_distance').value
        };

        try {
            await addExerciceSeance(payload);
            form.reset();
            closeExerciseModal();
            loadExercises(currentProgramId);
        } catch (e) {
            const box = document.getElementById('exerciseFormErrors');
            box.textContent = e.message || 'Erreur lors de l\'ajout de l\'exercice.';
            box.style.display = 'block';
        }
    }

    async function removeExercise(idExercice) {
        if (!confirm('Supprimer cet exercice ?')) {
            return;
        }
        try {
            await deleteExerciceSeance(idExercice);
            if (currentProgramId) {
                loadExercises(currentProgramId);
            }
        } catch (e) {
            alert(e.message || 'Suppression impossible.');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.btn-filter').addEventListener('click', applyFilters);
        document.querySelector('.btn-reset').addEventListener('click', resetFilters);
        document.getElementById('filter-search').addEventListener('input', applyFilters);
        document.getElementById('exerciseProgramSelect').addEventListener('change', function (e) {
            const value = e.target.value;
            if (!value) {
                currentProgramId = null;
                loadExercises(null);
                return;
            }
            selectProgram(Number(value));
        });
        document.getElementById('refreshExercisesBtn').addEventListener('click', function () {
            loadExercises(currentProgramId);
        });
        document.getElementById('openExerciseModalBtn').addEventListener('click', openExerciseModal);
        document.getElementById('exportCsvBtn').addEventListener('click', exportCurrentRowsCsv);
        document.getElementById('printBtn').addEventListener('click', function () {
            window.print();
        });
        document.getElementById('exerciseModalBackdrop').addEventListener('click', function (e) {
            if (e.target.id === 'exerciseModalBackdrop') {
                closeExerciseModal();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeExerciseModal();
            }
        });
        document.getElementById('exerciseForm').addEventListener('submit', submitExerciseForm);
        loadSeances();
    });

    window.selectProgram = selectProgram;
    window.removeExercise = removeExercise;
    window.closeExerciseModal = closeExerciseModal;
</script>

</body>
</html>
