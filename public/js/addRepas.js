/**
 * Validation du formulaire d'ajout/modification de repas
 * Trois couches : onClick, submit, temps réel
 */

// Utilitaires
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

// Validations individuelles
function validateIdPlan() {
    const val = document.getElementById('id_plan').value;
    if (!val) {
        showMsg('id_plan', 'Veuillez sélectionner un plan.', 'error');
        setFieldState('id_plan', false);
        return false;
    }
    showMsg('id_plan', 'Plan sélectionné.', 'success');
    setFieldState('id_plan', true);
    return true;
}

function validateJour() {
    const val = document.getElementById('jour').value;
    if (!val) {
        showMsg('jour', 'Veuillez sélectionner un jour.', 'error');
        setFieldState('jour', false);
        return false;
    }
    showMsg('jour', 'Repas prévu le ' + val, 'success');
    setFieldState('jour', true);
    return true;
}

function validateTypeRepas() {
    const val = document.getElementById('type_repas').value;
    if (!val) {
        showMsg('type_repas', 'Veuillez sélectionner un type de repas.', 'error');
        setFieldState('type_repas', false);
        return false;
    }
    const hints = {
        petit_dejeuner: '[*] Petit-déjeuner — Lever du soleil',
        dejeuner: '[O] Déjeuner — Plein soleil',
        diner: '[C] Dîner — Soirée',
        collation: '[>] Collation — Pomme'
    };
    showMsg('type_repas', hints[val], 'success');
    setFieldState('type_repas', true);
    return true;
}

function validateDescription() {
    const val = document.getElementById('description').value.trim();
    if (val.length < 10) {
        showMsg('description', 'La description doit contenir au moins 10 caractères (' + val.length + '/10).', 'error');
        setFieldState('description', false);
        return false;
    }
    showMsg('description', 'Description valide (' + val.length + ' caractères).', 'success');
    setFieldState('description', true);
    return true;
}

function validateKcal() {
    const val = parseInt(document.getElementById('kcal').value);
    if (!val || val < 50 || val > 2000) {
        showMsg('kcal', 'Les calories doivent être entre 50 et 2000.', 'error');
        setFieldState('kcal', false);
        return false;
    }
    showMsg('kcal', '= ' + val + ' kcal', 'success');
    setFieldState('kcal', true);
    return true;
}

// COUCHE 1 — onClick : alerte globale
document.getElementById('submitRepasBtn').addEventListener('click', function () {
    const errors = [];
    if (!document.getElementById('id_plan').value)
        errors.push('Plan alimentaire : sélection requise.');
    if (!document.getElementById('jour').value)
        errors.push('Jour : sélection requise.');
    if (!document.getElementById('type_repas').value)
        errors.push('Type de repas : sélection requise.');
    if (document.getElementById('description').value.trim().length < 10)
        errors.push('Description : minimum 10 caractères.');
    const kcal = parseInt(document.getElementById('kcal').value);
    if (!kcal || kcal < 50 || kcal > 2000)
        errors.push('Calories : doit être entre 50 et 2000 kcal.');

    if (errors.length > 0) {
        alert('Veuillez corriger les erreurs suivantes :\n\n' + errors.join('\n'));
    }
});

// COUCHE 2 — submit : messages par champ
document.getElementById('addRepasForm').addEventListener('submit', function (e) {
    const valid = [
        validateIdPlan(),
        validateJour(),
        validateTypeRepas(),
        validateDescription(),
        validateKcal()
    ];
    if (valid.includes(false)) {
        e.preventDefault();
    }
});

// COUCHE 3 — Temps réel
document.getElementById('description').addEventListener('keyup', validateDescription);

document.getElementById('kcal').addEventListener('input', validateKcal);

document.getElementById('type_repas').addEventListener('change', validateTypeRepas);

document.getElementById('jour').addEventListener('change', validateJour);
