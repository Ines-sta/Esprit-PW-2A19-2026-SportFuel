/**
 * Validation du formulaire d'ajout/modification de plan alimentaire
 * Trois couches : onClick, submit, temps réel
 */

// Utilitaires d'affichage des messages
function showMsg(id, message, type) {
    const el = document.getElementById('msg-' + id);
    if (!el) return;
    el.textContent = message;
    el.className = 'field-msg ' + type;
}

function setFieldState(id, valid) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('error', !valid);
    el.classList.toggle('success', valid);
}

// Validation individuelle de chaque champ
function validateNom() {
    const val = document.getElementById('nom').value.trim();
    if (val.length < 3) {
        showMsg('nom', 'Le nom doit contenir au moins 3 caractères.', 'error');
        setFieldState('nom', false);
        return false;
    }
    showMsg('nom', 'Nom valide.', 'success');
    setFieldState('nom', true);
    return true;
}

function validateIdUtilisateur() {
    const val = parseInt(document.getElementById('id_utilisateur').value);
    if (!val || val < 1) {
        showMsg('id_utilisateur', 'Veuillez saisir un ID utilisateur valide.', 'error');
        setFieldState('id_utilisateur', false);
        return false;
    }
    showMsg('id_utilisateur', 'ID valide.', 'success');
    setFieldState('id_utilisateur', true);
    return true;
}

function validateType() {
    const val = document.getElementById('type').value;
    if (!val) {
        showMsg('type', 'Veuillez sélectionner un type de plan.', 'error');
        setFieldState('type', false);
        return false;
    }
    const messages = {
        prise_de_masse: 'Objectif : +masse musculaire',
        perte_de_poids: 'Objectif : déficit calorique',
        maintien: 'Objectif : équilibre nutritionnel',
        endurance: 'Objectif : carburant longue durée'
    };
    showMsg('type', messages[val], 'success');
    setFieldState('type', true);
    return true;
}

function validateKcal() {
    const val = parseInt(document.getElementById('kcal_cibles').value);
    if (!val || val < 1000 || val > 6000) {
        showMsg('kcal_cibles', 'Les calories doivent être entre 1000 et 6000.', 'error');
        setFieldState('kcal_cibles', false);
        return false;
    }
    showMsg('kcal_cibles', val + ' kcal/jour', 'success');
    setFieldState('kcal_cibles', true);
    return true;
}

function validateSemaine() {
    const val = parseInt(document.getElementById('semaine').value);
    if (!val || val < 1 || val > 52) {
        showMsg('semaine', 'La semaine doit être entre 1 et 52.', 'error');
        setFieldState('semaine', false);
        return false;
    }
    showMsg('semaine', 'Semaine ' + val, 'success');
    setFieldState('semaine', true);
    return true;
}

function validateDateDebut() {
    const val = document.getElementById('date_debut').value;
    if (!val) {
        showMsg('date_debut', 'La date de début est requise.', 'error');
        setFieldState('date_debut', false);
        return false;
    }
    showMsg('date_debut', 'Date valide.', 'success');
    setFieldState('date_debut', true);
    return true;
}

function validateDateFin() {
    const debut = document.getElementById('date_debut').value;
    const fin = document.getElementById('date_fin').value;
    if (!fin) {
        showMsg('date_fin', 'La date de fin est requise.', 'error');
        setFieldState('date_fin', false);
        return false;
    }
    if (debut && fin <= debut) {
        showMsg('date_fin', 'La date de fin doit être après la date de début.', 'error');
        setFieldState('date_fin', false);
        return false;
    }
    showMsg('date_fin', 'Date de fin valide.', 'success');
    setFieldState('date_fin', true);
    return true;
}

// COUCHE 1 — onClick sur le bouton submit : alerte globale
document.getElementById('submitBtn').addEventListener('click', function () {
    const errors = [];
    if (!document.getElementById('nom').value.trim() || document.getElementById('nom').value.trim().length < 3)
        errors.push('Nom du plan : minimum 3 caractères.');
    if (!document.getElementById('id_utilisateur').value || parseInt(document.getElementById('id_utilisateur').value) < 1)
        errors.push('ID utilisateur : valeur requise.');
    if (!document.getElementById('type').value)
        errors.push('Type de plan : sélection requise.');
    const kcal = parseInt(document.getElementById('kcal_cibles').value);
    if (!kcal || kcal < 1000 || kcal > 6000)
        errors.push('Calories cibles : doit être entre 1000 et 6000 kcal.');
    const sem = parseInt(document.getElementById('semaine').value);
    if (!sem || sem < 1 || sem > 52)
        errors.push('Semaine : doit être entre 1 et 52.');
    if (!document.getElementById('date_debut').value)
        errors.push('Date de début : requise.');
    const debut = document.getElementById('date_debut').value;
    const fin = document.getElementById('date_fin').value;
    if (!fin)
        errors.push('Date de fin : requise.');
    else if (debut && fin <= debut)
        errors.push('Date de fin : doit être après la date de début.');

    if (errors.length > 0) {
        alert('Veuillez corriger les erreurs suivantes :\n\n' + errors.join('\n'));
    }
});

// COUCHE 2 — addEventListener submit : messages par champ
document.getElementById('addPlanForm').addEventListener('submit', function (e) {
    const valid = [
        validateNom(),
        validateIdUtilisateur(),
        validateType(),
        validateKcal(),
        validateSemaine(),
        validateDateDebut(),
        validateDateFin()
    ];
    if (valid.includes(false)) {
        e.preventDefault();
    }
});

// COUCHE 3 — Événements temps réel
document.getElementById('nom').addEventListener('keyup', validateNom);

document.getElementById('kcal_cibles').addEventListener('input', validateKcal);

document.getElementById('semaine').addEventListener('input', validateSemaine);

document.getElementById('date_fin').addEventListener('change', validateDateFin);
document.getElementById('date_debut').addEventListener('change', function () {
    validateDateDebut();
    if (document.getElementById('date_fin').value) validateDateFin();
});

document.getElementById('type').addEventListener('change', validateType);
