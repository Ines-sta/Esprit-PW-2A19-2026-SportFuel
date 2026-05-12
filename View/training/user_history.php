<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportFuel — Historique des entraînements</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel-main/public/css/style.css">
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel-main/public/css/entrainement.css">
    <script src="/Esprit-PW-2A19-2026-SportFuel-main/public/js/api.js"></script>
</head>
<body>

<?php
$navbarActive = 'training';
include __DIR__ . '/../partials/navbar.php';
?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h1>📜 Historique des entraînements</h1>
            <div class="page-date"><?php echo date('l j F Y'); ?></div>
        </div>
    </div>

    <div class="content-area training-content">

    <!-- Navigation buttons -->
    <div class="search-bar training-actions section-spacing">
        <a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=training" class="btn btn-primary">📅 Planifier une séance</a>
    </div>

    <!-- Résumé statistique -->
    <div class="stat-cards section-spacing">
        <div class="stat-card"><div class="stat-value" id="totalSessions">0</div><div class="stat-label">séances totales</div></div>
        <div class="stat-card"><div class="stat-value" id="monthSessions">0</div><div class="stat-label">ce mois</div></div>
        <div class="stat-card"><div class="stat-value" id="totalCalories">0 kcal</div><div class="stat-label">calories brûlées</div></div>
        <div class="stat-card"><div class="stat-value" id="avgRating">0/5</div><div class="stat-label">moyenne ressenti</div></div>
    </div>

    <!-- Barre de filtres -->
    <div class="search-bar training-filters section-spacing">
        <select id="filterType">
            <option>Tous les types</option>
        </select>
        <select id="filterIntensity">
            <option>Toutes intensités</option>
            <option>Faible</option>
            <option>Modérée</option>
            <option>Élevée</option>
        </select>
        <select id="filterPeriod">
            <option>Ce mois</option>
            <option>3 derniers mois</option>
            <option>2026</option>
            <option>Tout</option>
        </select>
        <button class="btn btn-primary btn-filter">Filtrer</button>
        <button class="btn btn-outline btn-reset">Reset</button>
    </div>

    <!-- Tableau historique -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Date</th><th>Type</th><th>Durée</th><th>Statut</th><th>Calories</th><th>Notes</th></tr>
            </thead>
            <tbody id="trainingTableBody">
                <tr><td colspan="6" style="text-align: center; padding: 20px; color: #999;">Chargement...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Graphique progression -->
    <div class="card section-card">
        <div class="card-header">📈 Progression mensuelle (calories brûlées)</div>
        <div class="section-card-content">
            <div class="progress-bar">
                <div class="progress-bar-fill"></div>
            </div>
            <div class="progress-labels">
                <span>Sem 1</span><span>Sem 2</span><span>Sem 3</span><span>Sem 4</span>
            </div>
        </div>
    </div>
</div>

</div>

<!-- FOOTER -->
<div class="footer">
    &copy; 2026 SportFuel — Nutrition intelligente pour sportifs
</div>
<script>
    window.SPORTFUEL_APP_BASE = `${window.location.origin}/Esprit-PW-2A19-2026-SportFuel`;
    window.SPORTFUEL_USER_ID = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;
</script>
<script>
    let allTrainings = [];

    function inferIntensity(training) {
        const duration = Number(training.duree_totale || 0);
        const status = String(training.statut || '').toLowerCase();
        if (status.includes('valid') && duration >= 60) return 'Élevée';
        if (duration >= 30) return 'Modérée';
        return 'Faible';
    }

    function normalizeNotes(value) {
        if (!value) return '—';
        const text = String(value)
            .replace(/__PROGRAMME__\|/g, '')
            .replace(/__PARTICIPATION__\|/g, '')
            .replace(/programme_id:[^|]+\|?/g, '')
            .replace(/programme_nom:[^|]+\|?/g, '')
            .replace(/\|/g, ' · ')
            .trim();
        return text.length > 45 ? text.substring(0, 45) + '...' : text;
    }

    function normalizeTypeLabel(value) {
        const source = String(value || '').trim();
        const cleaned = source
            .replace(/^[?\s]+/, '')
            .replace(/^[^\p{L}\p{N}]+/u, '')
            .trim();
        return cleaned || '—';
    }

    function populateTypeFilter() {
        const select = document.getElementById('filterType');
        const current = select.value;
        const types = Array.from(new Set(allTrainings.map(t => normalizeTypeLabel(t.titre)).filter(Boolean))).sort();
        select.innerHTML = '<option>Tous</option>' + types.map(t => `<option>${t}</option>`).join('');
        if (types.includes(current)) {
            select.value = current;
        }
    }

    function applyPeriodFilter(trainings, period) {
        const now = new Date();
        const month = now.getMonth();
        const year = now.getFullYear();

        if (period === 'Tout') return trainings;
        if (period === 'Ce mois') {
            return trainings.filter(t => {
                const d = new Date(t.date_entrainement);
                return d.getMonth() === month && d.getFullYear() === year;
            });
        }
        if (period === '3 derniers mois') {
            const cutoff = new Date(year, month - 2, 1);
            return trainings.filter(t => new Date(t.date_entrainement) >= cutoff);
        }
        if (/^\d{4}$/.test(period)) {
            return trainings.filter(t => new Date(t.date_entrainement).getFullYear() === Number(period));
        }
        return trainings;
    }

    // Charger les entraînements
    async function loadTrainings() {
        allTrainings = await getAllEntrainements();
        displayTrainings(allTrainings);
        updateStatistics();
    }

    // Afficher les entraînements dans le tableau
    function displayTrainings(trainings) {
        const tableBody = document.getElementById('trainingTableBody');

        if (trainings.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #999;">Aucun entraînement enregistré</td></tr>';
            return;
        }

        tableBody.innerHTML = trainings.map(training => {
            const date = new Date(training.date_entrainement).toLocaleDateString('fr-FR');
            const duration = Number(training.duree_totale || 0);
            const calories = duration * 7; // Approximation
            const notes = normalizeNotes(training.notes_globales);
            const titre = normalizeTypeLabel(training.titre);
            const statusClass = String(training.statut || '').toLowerCase().includes('valid')
                ? 'badge-actif'
                : (String(training.statut || '').toLowerCase().includes('attente') ? 'badge-en-attente' : 'badge-inactif');

            return `
                <tr>
                    <td>${date}</td>
                    <td>${titre}</td>
                    <td>${duration} min</td>
                    <td><span class="badge ${statusClass}">${training.statut}</span></td>
                    <td>${calories} kcal</td>
                    <td>${notes}</td>
                </tr>
            `;
        }).join('');
    }

    // Mettre à jour les statistiques
    function updateStatistics() {
        const totalSessions = allTrainings.length;
        const now = new Date();
        const currentMonth = now.getMonth();
        const currentYear = now.getFullYear();

        const monthSessions = allTrainings.filter(t => {
            const trainDate = new Date(t.date_entrainement);
            return trainDate.getMonth() === currentMonth && trainDate.getFullYear() === currentYear;
        }).length;

        const totalCalories = allTrainings.reduce((sum, t) => sum + (Number(t.duree_totale || 0) * 7), 0);
        const ratingSum = allTrainings.reduce((sum, t) => {
            const statut = String(t.statut || '').toLowerCase();
            if (statut.includes('valid')) return sum + 5;
            if (statut.includes('attente')) return sum + 3;
            return sum + 2;
        }, 0);
        const avgRating = (totalSessions > 0) ? (ratingSum / totalSessions).toFixed(1) : '0';

        document.getElementById('totalSessions').textContent = totalSessions;
        document.getElementById('monthSessions').textContent = monthSessions;
        document.getElementById('totalCalories').textContent = totalCalories + ' kcal';
        document.getElementById('avgRating').textContent = avgRating + '/5';
    }

    // Filtrer le tableau
    function filterTable() {
        const typeFilter = document.getElementById('filterType')?.value || 'Tous';
        const intensiteFilter = document.getElementById('filterIntensity')?.value || 'Toutes';
        const periodFilter = document.getElementById('filterPeriod')?.value || 'Ce mois';

        let filtered = allTrainings.filter(training => {
            const okType = (typeFilter === 'Tous' || normalizeTypeLabel(training.titre) === typeFilter);
            const okIntensity = (intensiteFilter === 'Toutes' || inferIntensity(training) === intensiteFilter);
            return okType && okIntensity;
        });

        filtered = applyPeriodFilter(filtered, periodFilter);
        displayTrainings(filtered);
    }

    // Réinitialiser les filtres
    function resetFilters() {
        const typeSelect = document.getElementById('filterType');
        const intensiteSelect = document.getElementById('filterIntensity');
        const periodeSelect = document.getElementById('filterPeriod');

        if (typeSelect) typeSelect.value = 'Tous';
        if (intensiteSelect) intensiteSelect.value = 'Toutes';
        if (periodeSelect) periodeSelect.value = 'Ce mois';

        displayTrainings(allTrainings);
    }

    // Attacher les événements
    const filterBtn = document.querySelector('.btn-filter');
    if (filterBtn) filterBtn.onclick = filterTable;

    const resetBtn = document.querySelector('.btn-reset');
    if (resetBtn) resetBtn.onclick = resetFilters;

    // Charger les données au démarrage
    loadTrainings().then(populateTypeFilter);
</script>
</body>
</html>