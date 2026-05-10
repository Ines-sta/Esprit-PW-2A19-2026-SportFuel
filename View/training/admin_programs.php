<?php
/**
 * BackOffice — Gestion des entraînements (Programmes)
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

$currentRole = sportfuel_current_role();
$isCoachRole = ($currentRole === 'Coach');
$currentCoachName = trim((string)($_SESSION['user_nom'] ?? ''));
if ($currentCoachName === '') {
    $currentCoachEmail = trim((string)($_SESSION['user_email'] ?? ''));
    $currentCoachName = $currentCoachEmail !== '' ? explode('@', $currentCoachEmail)[0] : 'Coach';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportFuel Admin — Gestion des entraînements</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel/public/css/style.css">
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel/public/css/entrainement.css">
    <style>
        .stats-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .stats-modal {
            width: min(900px, 92vw);
            max-height: 85vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.2);
            padding: 20px;
        }
        .stats-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .stats-circle-grid {
            display: block;
        }
        .stats-circle-card {
            border: 1px solid #eceff3;
            border-radius: 12px;
            padding: 18px;
            text-align: left;
            background: #fbfcff;
            max-width: 620px;
            margin: 0 auto;
        }
        .stats-circle {
            width: 220px;
            height: 220px;
            margin: 0 auto 14px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #111827;
            font-size: 14px;
            font-weight: 700;
            background: conic-gradient(#4f46e5 0deg, #e5e7eb 360deg);
        }
        .stats-percent {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 12px;
            text-align: center;
        }
        .stats-circle-label {
            font-size: 14px;
            color: #374151;
            font-weight: 600;
        }
        .stats-legend {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px 14px;
        }
        .stats-legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #374151;
        }
        .stats-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex: 0 0 auto;
        }

        /* Isolated modal styles for admin training forms (no shared CSS bleed). */
        .admin-program-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 1200;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .admin-program-overlay.active {
            display: flex;
        }

        .admin-program-modal {
            width: min(760px, 92vw);
            max-height: 88vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 14px;
            border: 1px solid var(--border, #e4e9e4);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.2);
            padding: 20px;
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
                <h1>🏋️ Gestion des entraînements</h1>
                <span class="date"><?php echo date('l j F Y', strtotime(date('Y-m-d'))); ?></span>
            </div>

            <div class="training-content">

            <!-- Cartes stats -->
            <div class="stat-cards section-spacing">
                <div class="stat-card"><div class="stat-value" id="stat-programs-total">0</div><div class="stat-label">Programmes totaux</div></div>
                <div class="stat-card"><div class="stat-value" id="stat-programs-active">0</div><div class="stat-label">Programmes actifs</div></div>
                <div class="stat-card"><div class="stat-value" id="stat-programs-completion">0%</div><div class="stat-label">Taux complétion</div></div>
                <div class="stat-card"><div class="stat-value" id="stat-programs-types">0</div><div class="stat-label">Types distincts</div></div>
            </div>

            <!-- Navigation buttons -->
            <div class="search-bar training-actions section-spacing">
                <button type="button" class="btn btn-primary" id="openProgramModalBtn">➕ Nouveau programme</button>
                <a href="/Esprit-PW-2A19-2026-SportFuel/index.php?page=training&view=sessions" class="btn btn-outline">⚙️ Gérer les séances</a>
            </div>

            <div class="search-bar training-filters section-spacing">
                <select id="programFilterType">
                    <option value="">Type: Tous</option>
                </select>
                <select id="programFilterStatus">
                    <option value="">Statut: Tous</option>
                    <option value="Validé">Validé</option>
                    <option value="En attente">En attente</option>
                    <option value="Inactif">Inactif</option>
                </select>
                <input type="text" id="programFilterSearch" placeholder="Nom du programme" autocomplete="off">
                <button type="button" class="btn btn-primary" onclick="applyFilters()">Filtrer</button>
                <button type="button" class="btn btn-outline" onclick="resetFilters()">Reset</button>
            </div>

            <!-- Tableau des programmes -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>ID</th><th>Nom</th><th>Sport</th><th>Date début</th><th>Niveau</th><th>Fréquence</th><th>Planifiées</th><th>Progression</th><th>Coach</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="programsTableBody">
                        <tr><td colspan="11" style="text-align:center; padding:16px;">Chargement...</td></tr>
                    </tbody>
                </table>
            </div>
            </div>
    </div>

    <div id="programModalBackdrop" class="modal-overlay admin-program-overlay" role="dialog" aria-modal="true" aria-labelledby="programModalTitle">
        <div class="modal sf-modal admin-program-modal">
            <div class="sf-modal-header">
                <h3 id="programModalTitle" style="margin:0;">➕ Nouveau programme d'entraînement</h3>
                <button type="button" class="btn btn-secondary" onclick="closeProgramModal()">Fermer</button>
            </div>
            <form action="#" method="post" id="programForm" novalidate>
                <div id="programFormErrors" role="alert" style="display:none; margin-bottom:16px; padding:12px 16px; background:#fde8e8; border:1px solid #f5c2c7; border-radius:8px; color:#842029; font-size:14px;"></div>
                <div class="form-row">
                    <div class="form-group"><label>Nom du programme *</label><input type="text" maxlength="120" placeholder="Ex: Prise de masse express" data-field="nom_programme" required autocomplete="off"></div>
                    <div class="form-group"><label>Sport cible *</label><select data-field="sport_cible" required>
                        <option value="">Sélectionnez un type</option>
                        <option>Musculation</option>
                        <option>Cardio</option>
                        <option>Course</option>
                        <option>Natation</option>
                        <option>Yoga</option>
                    </select></div>
                    <div class="form-group"><label>Date de début du programme *</label><input type="date" data-field="date_programme" id="date_programme" required></div>
                    <div class="form-group"><label>Niveau requis</label><select data-field="niveau"><option>Débutant</option><option>Intermédiaire</option><option>Avancé</option></select></div>
                    <div class="form-group"><label>Fréquence (séances / semaine) *</label><input type="number" min="1" max="14" step="1" inputmode="numeric" placeholder="ex: 3" data-field="frequence" required></div>
                    <div class="form-group"><label>Durée totale (semaines) *</label><input type="number" min="1" max="104" step="1" inputmode="numeric" placeholder="ex: 8" data-field="duree_semaines" required></div>
                    <div class="form-group"><label>Coach responsable *</label><select data-field="coach" id="programCoachSelect" required <?php echo $isCoachRole ? 'disabled' : ''; ?>><?php if ($isCoachRole): ?><option value="<?php echo htmlspecialchars($currentCoachName, ENT_QUOTES); ?>" selected><?php echo htmlspecialchars($currentCoachName); ?> (vous)</option><?php else: ?><option value="">Chargement des coachs...</option><?php endif; ?></select></div>
                    <div class="form-group"><label>Sportif cible (optionnel)</label><select data-field="sportif_cible" id="programSportifSelect"><option value="">Aucun(e) — Programme pour tous</option></select></div>
                    <div class="form-group"><label>Exercices max / programme *</label><input type="number" min="1" max="200" step="1" inputmode="numeric" placeholder="Ex: 10" data-field="max_exercices" required></div>
                </div>
                <button type="submit" class="btn btn-primary">📌 Créer le programme</button>
            </form>
        </div>
    </div>

    <div id="editProgramModalBackdrop" class="modal-overlay admin-program-overlay" role="dialog" aria-modal="true" aria-labelledby="editProgramModalTitle">
        <div class="modal sf-modal admin-program-modal">
            <div class="sf-modal-header">
                <h3 id="editProgramModalTitle" style="margin:0;">✏️ Modifier le programme</h3>
                <button type="button" class="btn btn-secondary" onclick="closeEditProgramModal()">Fermer</button>
            </div>
            <form action="#" method="post" id="editProgramForm" novalidate>
                <input type="hidden" id="edit_program_id">
                <div id="editProgramFormErrors" role="alert" style="display:none; margin-bottom:16px; padding:12px 16px; background:#fde8e8; border:1px solid #f5c2c7; border-radius:8px; color:#842029; font-size:14px;"></div>
                <div class="form-row">
                    <div class="form-group"><label>Nom du programme *</label><input type="text" id="edit_nom_programme" maxlength="120" required></div>
                    <div class="form-group"><label>Sport cible *</label><select id="edit_sport_cible" required>
                        <option>Musculation</option>
                        <option>Cardio</option>
                        <option>Course</option>
                        <option>Natation</option>
                        <option>Yoga</option>
                    </select></div>
                    <div class="form-group"><label>Date de début *</label><input type="date" id="edit_date_programme" required></div>
                    <div class="form-group"><label>Statut *</label><select id="edit_statut" required><option>Validé</option><option>En attente</option><option>Inactif</option></select></div>
                    <div class="form-group"><label>Niveau requis</label><select id="edit_niveau"><option>Débutant</option><option>Intermédiaire</option><option>Avancé</option></select></div>
                    <div class="form-group"><label>Fréquence *</label><input type="number" id="edit_frequence" min="1" max="14" step="1" required></div>
                    <div class="form-group"><label>Durée (semaines) *</label><input type="number" id="edit_duree_semaines" min="1" max="104" step="1" required></div>
                    <div class="form-group"><label>Coach responsable *</label><select id="edit_coach" required <?php echo $isCoachRole ? 'disabled' : ''; ?>><?php if ($isCoachRole): ?><option value="<?php echo htmlspecialchars($currentCoachName, ENT_QUOTES); ?>" selected><?php echo htmlspecialchars($currentCoachName); ?> (vous)</option><?php else: ?><option value="">Chargement des coachs...</option><?php endif; ?></select></div>
                    <div class="form-group"><label>Sportif cible (optionnel)</label><select id="edit_sportif_cible"><option value="">Aucun(e) — Programme pour tous</option></select></div>
                    <div class="form-group"><label>Exercices max *</label><input type="number" id="edit_max_exercices" min="1" max="200" step="1" required></div>
                </div>
                <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
            </form>
        </div>
    </div>

<div id="statsModalBackdrop" class="stats-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="statsModalTitle">
    <div class="stats-modal">
        <div class="stats-modal-header">
            <h3 id="statsModalTitle" style="margin:0;">📊 Statistiques des types d'exercices</h3>
            <button type="button" class="btn btn-secondary" onclick="closeStatsModal()">Fermer</button>
        </div>
        <div id="statsCircleGrid" class="stats-circle-grid"></div>
    </div>
</div>

<script src="/Esprit-PW-2A19-2026-SportFuel/public/js/api.js"></script>
<script src="/Esprit-PW-2A19-2026-SportFuel/public/js/validation.js"></script>
<script>
    const APP_BASE = `${window.location.origin}/Esprit-PW-2A19-2026-SportFuel`;
    const IS_COACH_ROLE = <?= $isCoachRole ? 'true' : 'false' ?>;
    const LOGGED_COACH_NAME = <?= json_encode($currentCoachName, JSON_UNESCAPED_UNICODE) ?>;
    let allPrograms = [];
    let filteredPrograms = [];
    let coachUsers = [];

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function statusBadgeClass(statut) {
        const s = String(statut || '').toLowerCase();
        if (s.includes('valid')) return 'badge-actif';
        if (s.includes('attente')) return 'badge-en-attente';
        if (s.includes('actif')) return 'badge-actif';
        return 'badge-inactif';
    }

    function formatDateFr(dateRaw) {
        if (!dateRaw) return '—';
        const d = new Date(`${dateRaw}T00:00:00`);
        if (Number.isNaN(d.getTime())) return '—';
        return d.toLocaleDateString('fr-FR');
    }

    function calcPlannedSessions(program) {
        const f = parseInt(program.frequence, 10);
        const w = parseInt(program.duree_semaines, 10);
        if (Number.isNaN(f) || Number.isNaN(w) || f < 1 || w < 1) return '—';
        return String(f * w);
    }

    function completionRate(program) {
        const statut = String(program.statut || '').toLowerCase();
        if (statut.includes('valid')) return 100;
        if (statut.includes('attente')) return 40;
        return 15;
    }

    function updateStats(rows) {
        const total = rows.length;
        const active = rows.filter(r => String(r.statut || '').toLowerCase().includes('valid')).length;
        const avgCompletion = total > 0 ? Math.round(rows.reduce((acc, r) => acc + completionRate(r), 0) / total) : 0;
        const typesCount = new Set(rows.map(r => String(r.titre || '').trim().toLowerCase()).filter(Boolean)).size;

        document.getElementById('stat-programs-total').textContent = String(total);
        document.getElementById('stat-programs-active').textContent = String(active);
        document.getElementById('stat-programs-completion').textContent = `${avgCompletion}%`;
        document.getElementById('stat-programs-types').textContent = String(typesCount);
    }

    function renderTable(rows) {
        const tbody = document.getElementById('programsTableBody');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="11" style="text-align:center; padding:16px;">Aucun programme trouvé.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map((p) => {
            const completion = completionRate(p);
            return `
                <tr>
                    <td>${escapeHtml(p.id_entrainement)}</td>
                    <td>${escapeHtml(p.libelle_programme || 'Programme #' + p.id_entrainement)}</td>
                    <td>${escapeHtml(p.titre || '—')}</td>
                    <td>${formatDateFr(p.date_entrainement)}</td>
                    <td>${escapeHtml(p.niveau || '—')}</td>
                    <td>${escapeHtml(p.frequence || '—')}</td>
                    <td>${escapeHtml(calcPlannedSessions(p))}</td>
                    <td>${completion}%</td>
                    <td>${escapeHtml(p.coach || '—')}</td>
                    <td><span class="badge ${statusBadgeClass(p.statut)}">${escapeHtml(p.statut || 'Inactif')}</span></td>
                    <td>
                        <button class="btn btn-secondary" type="button" onclick="editProgram(${Number(p.id_entrainement)})">Modifier</button>
                        <button class="btn btn-secondary" type="button" onclick="showProgramStats(${Number(p.id_entrainement)})">Stats</button>
                        <button class="btn btn-secondary" type="button" onclick="removeProgram(${Number(p.id_entrainement)})">Supprimer</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function fillTypeFilter(rows) {
        const typeSelect = document.getElementById('programFilterType');
        const previous = typeSelect.value;
        const types = Array.from(new Set(rows.map(r => String(r.titre || '').trim()).filter(Boolean))).sort();
        typeSelect.innerHTML = '<option value="">Tous</option>' + types.map(t => `<option value="${escapeHtml(t)}">${escapeHtml(t)}</option>`).join('');
        if (types.includes(previous)) {
            typeSelect.value = previous;
        }
    }

    function applyFilters() {
        const type = document.getElementById('programFilterType').value;
        const statut = document.getElementById('programFilterStatus').value;
        const search = document.getElementById('programFilterSearch').value.trim().toLowerCase();
        filteredPrograms = allPrograms.filter((p) => {
            if (type && String(p.titre || '').trim() !== type) {
                return false;
            }
            if (statut && String(p.statut || '').trim() !== statut) {
                return false;
            }
            const programName = String(p.libelle_programme || '').toLowerCase();
            if (search && !programName.includes(search)) {
                return false;
            }
            return true;
        });
        renderTable(filteredPrograms);
        updateStats(filteredPrograms);
    }

    function resetFilters() {
        document.getElementById('programFilterType').value = '';
        document.getElementById('programFilterStatus').value = '';
        document.getElementById('programFilterSearch').value = '';
        filteredPrograms = allPrograms.slice();
        renderTable(filteredPrograms);
        updateStats(filteredPrograms);
    }

    async function loadPrograms() {
        try {
            const programs = await getProgrammes();
            allPrograms = Array.isArray(programs) ? programs : [];
            filteredPrograms = allPrograms.slice();
            fillTypeFilter(allPrograms);
            renderTable(filteredPrograms);
            updateStats(filteredPrograms);
        } catch (e) {
            document.getElementById('programsTableBody').innerHTML =
                '<tr><td colspan="11" style="text-align:center;">Erreur de chargement des données.</td></tr>';
        }
    }

    async function loadCoaches() {
        if (IS_COACH_ROLE) {
            coachUsers = [{ nom: LOGGED_COACH_NAME, email: '' }];
            const createSelect = document.getElementById('programCoachSelect');
            const editSelect = document.getElementById('edit_coach');
            const label = `${escapeHtml(LOGGED_COACH_NAME)} (vous)`;
            createSelect.innerHTML = `<option value="${escapeHtml(LOGGED_COACH_NAME)}" selected>${label}</option>`;
            editSelect.innerHTML = `<option value="${escapeHtml(LOGGED_COACH_NAME)}" selected>${label}</option>`;
            createSelect.value = LOGGED_COACH_NAME;
            editSelect.value = LOGGED_COACH_NAME;
            await loadSportifs();
            return;
        }

        try {
            coachUsers = await getCoachUsers();
            const activeCoaches = coachUsers.filter((c) => String(c.statut || '').toLowerCase() !== 'inactif');
            const createSelect = document.getElementById('programCoachSelect');
            const editSelect = document.getElementById('edit_coach');
            if (!activeCoaches.length) {
                createSelect.innerHTML = '<option value="">Aucun coach disponible</option>';
                editSelect.innerHTML = '<option value="">Aucun coach disponible</option>';
                return;
            }
            const optionsHtml = '<option value="">Sélectionnez un coach</option>' + activeCoaches.map((coach) => (
                `<option value="${escapeHtml(coach.nom)}">${escapeHtml(coach.nom)} (${escapeHtml(coach.email)})</option>`
            )).join('');
            createSelect.innerHTML = optionsHtml;
            editSelect.innerHTML = optionsHtml;
            await loadSportifs();
        } catch (e) {
            document.getElementById('programCoachSelect').innerHTML = '<option value="">Erreur de chargement des coachs</option>';
            document.getElementById('edit_coach').innerHTML = '<option value="">Erreur de chargement des coachs</option>';
        }
    }

    async function loadSportifs(coachId = null) {
        try {
            let url = `${APP_BASE}/includes/get_sportifs.php`;
            if (coachId !== null && coachId > 0) {
                url += `?coach_id=${encodeURIComponent(coachId)}`;
            } else if (!IS_COACH_ROLE) {
                // For admin, get all sportifs
                url += '?all=1';
            }
            const response = await fetch(url);
            const result = await response.json();
            const sportifs = Array.isArray(result.data) ? result.data : [];
            
            const createSelect = document.getElementById('programSportifSelect');
            const editSelect = document.getElementById('edit_sportif_cible');
            
            const baseoption = '<option value="">Aucun(e) — Programme pour tous</option>';
            const options = sportifs.filter((s) => String(s.statut || '').toLowerCase() !== 'inactif')
                .map((s) => `<option value="${escapeHtml(s.id)}">${escapeHtml(s.nom)} (${escapeHtml(s.sport_pratique || 'N/A')})</option>`)
                .join('');
            
            createSelect.innerHTML = baseoption + options;
            editSelect.innerHTML = baseoption + options;
        } catch (e) {
            // Silently fail; sportif selection is optional
            const createSelect = document.getElementById('programSportifSelect');
            const editSelect = document.getElementById('edit_sportif_cible');
            createSelect.innerHTML = '<option value="">Erreur de chargement des sportifs</option>';
            editSelect.innerHTML = '<option value="">Erreur de chargement des sportifs</option>';
        }
    }

    async function createProgramFromForm() {
        const form = document.getElementById('programForm');
        if (!validerFormProgramme(form)) {
            return;
        }

        const nom = form.querySelector('[data-field="nom_programme"]').value.trim();
        const sport = form.querySelector('[data-field="sport_cible"]').value.trim();
        const date = form.querySelector('[data-field="date_programme"]').value;
        const niveau = form.querySelector('[data-field="niveau"]').value.trim();
        const frequence = form.querySelector('[data-field="frequence"]').value.trim();
        const dureeSemaines = form.querySelector('[data-field="duree_semaines"]').value.trim();
        const coach = IS_COACH_ROLE
            ? LOGGED_COACH_NAME
            : form.querySelector('[data-field="coach"]').value.trim();
        const maxExercices = form.querySelector('[data-field="max_exercices"]').value.trim();
        const sportifCible = form.querySelector('[data-field="sportif_cible"]').value.trim();

        let notes = `__PROGRAMME__|${nom}|Niveau: ${niveau}|Fréquence: ${frequence}|Durée: ${dureeSemaines}|Coach: ${coach || 'N/A'}|MaxExercices: ${maxExercices}`;
        if (sportifCible !== '') {
            notes += `|CibleSportif: ${sportifCible}`;
        }

        try {
            await createEntrainement(sport, date, null, notes);
            form.reset();
            closeProgramModal();
            loadPrograms();
        } catch (e) {
            const errorBox = document.getElementById('programFormErrors');
            errorBox.textContent = e.message || 'Erreur lors de la création du programme.';
            errorBox.style.display = 'block';
        }
    }

    async function removeProgram(id) {
        if (!confirm('Supprimer ce programme ?')) {
            return;
        }
        try {
            await deleteEntrainement(id);
            await loadPrograms();
        } catch (e) {
            alert(e.message || 'Suppression impossible');
        }
    }

    function parseProgramNotes(raw) {
        const text = String(raw || '');
        const result = {
            nom: '',
            niveau: 'Débutant',
            frequence: 3,
            duree_semaines: 8,
            coach: '',
            max_exercices: 10,
            cible_sportif: ''
        };

        const parts = text.split('|').map(p => p.trim());
        if (parts.length > 1 && parts[0] === '__PROGRAMME__') {
            result.nom = parts[1] || '';
        }
        parts.forEach((part) => {
            if (part.startsWith('Niveau:')) result.niveau = part.replace('Niveau:', '').trim() || result.niveau;
            if (part.startsWith('Fréquence:')) result.frequence = parseInt(part.replace('Fréquence:', '').trim(), 10) || result.frequence;
            if (part.startsWith('Durée:')) result.duree_semaines = parseInt(part.replace('Durée:', '').trim(), 10) || result.duree_semaines;
            if (part.startsWith('Coach:')) result.coach = part.replace('Coach:', '').trim();
            if (part.startsWith('MaxExercices:')) result.max_exercices = parseInt(part.replace('MaxExercices:', '').trim(), 10) || result.max_exercices;
            if (part.startsWith('CibleSportif:')) result.cible_sportif = part.replace('CibleSportif:', '').trim();
        });
        return result;
    }

    async function editProgram(id) {
        const program = allPrograms.find((p) => Number(p.id_entrainement) === Number(id));
        if (!program) {
            return;
        }

        const parsed = parseProgramNotes(program.notes_globales);
        document.getElementById('edit_program_id').value = String(program.id_entrainement || '');
        document.getElementById('edit_nom_programme').value = parsed.nom || program.libelle_programme || '';
        document.getElementById('edit_sport_cible').value = String(program.titre || 'Musculation');
        document.getElementById('edit_date_programme').value = String(program.date_entrainement || '');
        document.getElementById('edit_statut').value = String(program.statut || 'En attente');
        document.getElementById('edit_niveau').value = parsed.niveau || 'Débutant';
        document.getElementById('edit_frequence').value = parsed.frequence || 3;
        document.getElementById('edit_duree_semaines').value = parsed.duree_semaines || 8;
        document.getElementById('edit_coach').value = IS_COACH_ROLE ? LOGGED_COACH_NAME : (parsed.coach || program.coach || '');
        document.getElementById('edit_max_exercices').value = parsed.max_exercices || 10;
        document.getElementById('edit_sportif_cible').value = parsed.cible_sportif || '';
        openEditProgramModal();
    }

    async function submitEditProgramForm(event) {
        event.preventDefault();
        const id = Number(document.getElementById('edit_program_id').value);
        if (!id) return;

        const nom = document.getElementById('edit_nom_programme').value.trim();
        const sport = document.getElementById('edit_sport_cible').value.trim();
        const date = document.getElementById('edit_date_programme').value;
        const statut = document.getElementById('edit_statut').value.trim();
        const niveau = document.getElementById('edit_niveau').value.trim();
        const frequence = document.getElementById('edit_frequence').value.trim();
        const dureeSemaines = document.getElementById('edit_duree_semaines').value.trim();
        const coach = IS_COACH_ROLE
            ? LOGGED_COACH_NAME
            : document.getElementById('edit_coach').value.trim();
        const maxExercices = document.getElementById('edit_max_exercices').value.trim();
        const sportifCible = document.getElementById('edit_sportif_cible').value.trim();

        if (!nom || !sport || !date || !statut || !coach) {
            const box = document.getElementById('editProgramFormErrors');
            box.textContent = 'Veuillez remplir tous les champs obligatoires.';
            box.style.display = 'block';
            return;
        }

        let notes = `__PROGRAMME__|${nom}|Niveau: ${niveau}|Fréquence: ${frequence}|Durée: ${dureeSemaines}|Coach: ${coach}|MaxExercices: ${maxExercices}`;
        if (sportifCible !== '') {
            notes += `|CibleSportif: ${sportifCible}`;
        }

        try {
            await updateEntrainement(id, {
                titre: sport,
                date_entrainement: date,
                statut: statut,
                notes: notes
            });
            closeEditProgramModal();
            await loadPrograms();
        } catch (e) {
            const box = document.getElementById('editProgramFormErrors');
            box.textContent = e.message || 'Modification impossible';
            box.style.display = 'block';
        }
    }

    function showProgramStats(id) {
        const program = allPrograms.find((p) => Number(p.id_entrainement) === Number(id));
        if (!program) {
            return;
        }
        const completion = completionRate(program);
        const remaining = Math.max(0, 100 - completion);
        document.getElementById('statsCircleGrid').innerHTML = `
            <div class="stats-circle-card">
                <div class="stats-circle" style="background: conic-gradient(#4f46e5 0deg, #4f46e5 ${completion * 3.6}deg, #e5e7eb ${completion * 3.6}deg, #e5e7eb 360deg)">
                    ${escapeHtml(program.libelle_programme || 'Programme #' + program.id_entrainement)}
                </div>
                <div class="stats-percent">${completion}% complété</div>
                <div class="stats-legend">
                    <div class="stats-legend-item"><span class="stats-dot" style="background:#4f46e5;"></span>Complété: ${completion}%</div>
                    <div class="stats-legend-item"><span class="stats-dot" style="background:#e5e7eb;"></span>Restant: ${remaining}%</div>
                    <div class="stats-legend-item"><span class="stats-dot" style="background:#16a34a;"></span>Statut: ${escapeHtml(program.statut || 'Inactif')}</div>
                    <div class="stats-legend-item"><span class="stats-dot" style="background:#0ea5e9;"></span>Type: ${escapeHtml(program.titre || '—')}</div>
                </div>
            </div>
        `;
        document.getElementById('statsModalBackdrop').style.display = 'flex';
    }

    function closeStatsModal() {
        document.getElementById('statsModalBackdrop').style.display = 'none';
    }

    function openProgramModal() {
        const errorBox = document.getElementById('programFormErrors');
        errorBox.style.display = 'none';
        errorBox.textContent = '';
        document.getElementById('programModalBackdrop').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeProgramModal() {
        document.getElementById('programModalBackdrop').classList.remove('active');
        document.body.style.overflow = '';
    }

    function openEditProgramModal() {
        const box = document.getElementById('editProgramFormErrors');
        box.style.display = 'none';
        box.textContent = '';
        document.getElementById('editProgramModalBackdrop').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeEditProgramModal() {
        document.getElementById('editProgramModalBackdrop').classList.remove('active');
        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', async function() {
        const form = document.getElementById('programForm');
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            createProgramFromForm();
        });
        document.getElementById('editProgramForm').addEventListener('submit', submitEditProgramForm);
        document.getElementById('openProgramModalBtn').addEventListener('click', openProgramModal);
        document.getElementById('programModalBackdrop').addEventListener('click', function (e) {
            if (e.target.id === 'programModalBackdrop') {
                closeProgramModal();
            }
        });
        document.getElementById('editProgramModalBackdrop').addEventListener('click', function (e) {
            if (e.target.id === 'editProgramModalBackdrop') {
                closeEditProgramModal();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeProgramModal();
                closeEditProgramModal();
            }
        });
        document.getElementById('programFilterSearch').addEventListener('input', applyFilters);
        document.getElementById('programFilterType').addEventListener('change', applyFilters);
        document.getElementById('programFilterStatus').addEventListener('change', applyFilters);
        await loadCoaches();
        loadPrograms();
    });

    window.closeStatsModal = closeStatsModal;
    window.removeProgram = removeProgram;
    window.showProgramStats = showProgramStats;
    window.applyFilters = applyFilters;
    window.resetFilters = resetFilters;
    window.editProgram = editProgram;
    window.closeProgramModal = closeProgramModal;
    window.closeEditProgramModal = closeEditProgramModal;
</script>

</body>
</html>
