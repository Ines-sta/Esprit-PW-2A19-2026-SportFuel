// SportFuel shared training API helpers
(function () {
    const APP_BASE = window.SPORTFUEL_APP_BASE || `${window.location.origin}/Esprit-PW-2A19-2026-SportFuel`;
    const INCLUDES_BASE = `${APP_BASE}/includes`;

    function currentUserId() {
        const id = window.SPORTFUEL_USER_ID;
        return (id === null || id === undefined || id === '' || Number.isNaN(Number(id))) ? null : Number(id);
    }

    async function fetchJson(url, options) {
        const response = await fetch(url, options || {});
        const raw = await response.text();
        let data = {};
        try {
            data = raw ? JSON.parse(raw) : {};
        } catch (e) {
            throw new Error('Réponse JSON invalide');
        }
        if (!response.ok) {
            throw new Error(data.error || data.message || 'Erreur serveur');
        }
        return data;
    }

    function toFormBody(payload) {
        const params = new URLSearchParams();
        Object.keys(payload).forEach(function (key) {
            const value = payload[key];
            if (value !== undefined && value !== null && value !== '') {
                params.append(key, value);
            }
        });
        return params;
    }

    async function getProgrammes() {
        const data = await fetchJson(`${INCLUDES_BASE}/get_programmes.php`);
        return Array.isArray(data.data) ? data.data : [];
    }

    async function getCoachUsers() {
        const data = await fetchJson(`${INCLUDES_BASE}/get_coaches.php`);
        return Array.isArray(data.data) ? data.data : [];
    }

    async function getAllEntrainements() {
        const data = await fetchJson(`${INCLUDES_BASE}/list_entrainements.php`);
        const rows = Array.isArray(data.data) ? data.data : [];
        const uid = currentUserId();
        if (!uid) {
            return rows;
        }
        return rows.filter(function (row) {
            return Number(row.id_utilisateur) === uid;
        });
    }

    async function createEntrainement(titre, date_entrainement, duree_totale, notes_globales) {
        const safeDuration = (duree_totale === null || duree_totale === undefined || duree_totale === '')
            ? 0
            : Number(duree_totale);
        const payload = {
            id_utilisateur: currentUserId(),
            titre: titre,
            date_entrainement: date_entrainement,
            duree_totale: Number.isNaN(safeDuration) ? 0 : safeDuration,
            notes: notes_globales
        };
        return fetchJson(`${INCLUDES_BASE}/add_entrainement.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: toFormBody(payload)
        });
    }

    async function updateEntrainement(id_entrainement, fields) {
        const payload = Object.assign({ id_entrainement: id_entrainement }, fields || {});
        return fetchJson(`${INCLUDES_BASE}/update_entrainement.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: toFormBody(payload)
        });
    }

    async function deleteEntrainement(id_entrainement) {
        return fetchJson(`${INCLUDES_BASE}/delete_entrainement.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: toFormBody({ id_entrainement: id_entrainement })
        });
    }

    async function listExercicesSeance(id_entrainement) {
        const data = await fetchJson(`${INCLUDES_BASE}/list_exercices_seance.php?id_entrainement=${encodeURIComponent(id_entrainement)}`);
        return Array.isArray(data.data) ? data.data : [];
    }

    async function addExerciceSeance(payload) {
        return fetchJson(`${INCLUDES_BASE}/add_exercice_seance.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: toFormBody(payload)
        });
    }

    async function deleteExerciceSeance(id_exercice_seance) {
        return fetchJson(`${INCLUDES_BASE}/delete_exercice_seance.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: toFormBody({ id_exercice_seance: id_exercice_seance })
        });
    }

    async function getRecommendedProgrammes() {
        const programmes = await getProgrammes();
        const uid = currentUserId();
        const base = uid ? programmes.filter(function (p) { return Number(p.id_utilisateur) !== uid; }) : programmes;
        return base.slice(0, 4).map(function (p) {
            return Object.assign({}, p, { reasons: ['Basé sur les programmes les plus récents'] });
        });
    }

    window.getProgrammes = getProgrammes;
    window.getCoachUsers = getCoachUsers;
    window.getAllEntrainements = getAllEntrainements;
    window.createEntrainement = createEntrainement;
    window.updateEntrainement = updateEntrainement;
    window.deleteEntrainement = deleteEntrainement;
    window.listExercicesSeance = listExercicesSeance;
    window.addExerciceSeance = addExerciceSeance;
    window.deleteExerciceSeance = deleteExerciceSeance;
    window.getRecommendedProgrammes = getRecommendedProgrammes;
})();
