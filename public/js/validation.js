// ============================================
// SportFuel — Validation JavaScript côté client
// Pas de validation HTML5 (contrainte du projet)
// ============================================

function setFormSubmittingState(form, isSubmitting, loadingLabel) {
    var submitBtn = form.querySelector('button[type="submit"]');
    if (!submitBtn) return;

    if (!submitBtn.dataset.originalHtml) {
        submitBtn.dataset.originalHtml = submitBtn.innerHTML;
    }

    if (isSubmitting) {
        var safeLabel = loadingLabel || 'Envoi en cours...';
        submitBtn.disabled = true;
        submitBtn.classList.add('is-submitting');
        submitBtn.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span><span>' + safeLabel + '</span>';
    } else {
        submitBtn.disabled = false;
        submitBtn.classList.remove('is-submitting');
        submitBtn.innerHTML = submitBtn.dataset.originalHtml;
    }
}

function finalizeFormValidation(form, erreurs, loadingLabel) {
    var isValid = afficherErreurs(form, erreurs);
    if (!isValid) {
        setFormSubmittingState(form, false);
        return false;
    }

    setFormSubmittingState(form, true, loadingLabel);
    return true;
}

function isValidIsoDate(dateValue) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(dateValue)) {
        return false;
    }

    var parts = dateValue.split('-');
    var year = parseInt(parts[0], 10);
    var month = parseInt(parts[1], 10);
    var day = parseInt(parts[2], 10);
    var parsed = new Date(Date.UTC(year, month - 1, day));

    return parsed.getUTCFullYear() === year &&
        parsed.getUTCMonth() === month - 1 &&
        parsed.getUTCDate() === day;
}

/**
 * Valider le formulaire Aliment (ajout et modification)
 */
function validerFormAliment(form) {
    var nom = form.querySelector('[name="nom"]').value.trim();
    var categorie = form.querySelector('[name="categorie"]').value.trim();
    var kcal = form.querySelector('[name="kcal_portion"]').value.trim();
    var co2 = form.querySelector('[name="co2_impact"]').value.trim();
    var prix = form.querySelector('[name="prix_unitaire"]').value.trim();
    var erreurs = [];

    if (nom === '') {
        erreurs.push("Le nom de l'aliment est obligatoire.");
    } else if (nom.length > 150) {
        erreurs.push("Le nom ne doit pas dépasser 150 caractères.");
    }

    if (categorie === '') {
        erreurs.push("La catégorie est obligatoire.");
    } else if (categorie.length > 100) {
        erreurs.push("La catégorie ne doit pas dépasser 100 caractères.");
    }

    if (kcal === '') {
        erreurs.push("Les calories sont obligatoires.");
    } else if (isNaN(kcal) || parseFloat(kcal) <= 0) {
        erreurs.push("Les calories doivent être un nombre positif.");
    }

    if (co2 === '') {
        erreurs.push("L'impact CO₂ est obligatoire.");
    } else if (isNaN(co2) || parseFloat(co2) < 0) {
        erreurs.push("L'impact CO₂ doit être un nombre positif.");
    }

    if (prix === '') {
        erreurs.push("Le prix unitaire est obligatoire.");
    } else if (isNaN(prix) || parseFloat(prix) <= 0) {
        erreurs.push("Le prix unitaire doit être un nombre positif.");
    }

    return finalizeFormValidation(form, erreurs, 'Enregistrement...');
}

/**
 * Valider le formulaire Course (ajout et modification d'une liste)
 */
function validerFormCourse(form) {
    var nom = form.querySelector('[name="nom"]').value.trim();
    var idUser = form.querySelector('[name="id_utilisateur"]').value.trim();
    var date = form.querySelector('[name="date"]').value.trim();
    var statut = form.querySelector('[name="statut"]').value.trim();
    var erreurs = [];

    if (nom === '') {
        erreurs.push("Le nom de la liste est obligatoire.");
    } else if (nom.length > 150) {
        erreurs.push("Le nom ne doit pas dépasser 150 caractères.");
    }

    if (idUser === '' || idUser === '0') {
        erreurs.push("Veuillez sélectionner un utilisateur.");
    }

    if (date === '') {
        erreurs.push("La date est obligatoire.");
    } else if (!isValidIsoDate(date)) {
        erreurs.push("La date doit être au format AAAA-MM-JJ.");
    }

    if (statut === '') {
        erreurs.push("Le statut est obligatoire.");
    }

    return finalizeFormValidation(form, erreurs, 'Enregistrement...');
}

/**
 * Valider le formulaire d'ajout d'article à une course
 */
function validerFormArticle(form) {
    var aliment = form.querySelector('[name="id_aliment"]').value.trim();
    var quantite = form.querySelector('[name="quantite"]').value.trim();
    var uniteEl = form.querySelector('[name="unite"]');
    var unite = uniteEl ? uniteEl.value.trim() : 'g';
    var unitesValides = ['g', 'kg', 'ml', 'L', 'piece'];
    var erreurs = [];

    if (aliment === '') {
        erreurs.push("Veuillez sélectionner un aliment.");
    }

    if (quantite === '') {
        erreurs.push("La quantité est obligatoire.");
    } else if (isNaN(quantite) || parseFloat(quantite) <= 0) {
        erreurs.push("La quantité doit être un nombre positif.");
    }

    if (unitesValides.indexOf(unite) === -1) {
        erreurs.push("Unité invalide.");
    }

    return afficherErreurs(form, erreurs);
}

/**
 * Helper : affichage des messages d'erreur dans le <div id="erreur*"> du formulaire.
 */
function afficherErreurs(form, erreurs) {
    var erreurDiv = form.querySelector('[id^="erreur"]');
    if (erreurs.length > 0) {
        if (erreurDiv) {
            erreurDiv.textContent = erreurs.join(' ');
            erreurDiv.style.display = 'block';
        }
        return false;
    }
    if (erreurDiv) {
        erreurDiv.style.display = 'none';
    }
    return true;
}

window.addEventListener('pageshow', function () {
    var buttons = document.querySelectorAll('button.is-submitting');
    for (var i = 0; i < buttons.length; i++) {
        var btn = buttons[i];
        btn.disabled = false;
        btn.classList.remove('is-submitting');
        if (btn.dataset.originalHtml) {
            btn.innerHTML = btn.dataset.originalHtml;
        }
    }
});

/**
 * Valider le formulaire Programme d'entraînement (admin)
 */
function validerFormProgramme(form) {
    var nom = (form.querySelector('[data-field="nom_programme"]') || {}).value || '';
    var sport = (form.querySelector('[data-field="sport_cible"]') || {}).value || '';
    var dateProgramme = (form.querySelector('[data-field="date_programme"]') || {}).value || '';
    var coach = (form.querySelector('[data-field="coach"]') || {}).value || '';
    var frequence = (form.querySelector('[data-field="frequence"]') || {}).value || '';
    var dureeSemaines = (form.querySelector('[data-field="duree_semaines"]') || {}).value || '';
    var maxExercices = (form.querySelector('[data-field="max_exercices"]') || {}).value || '';
    var erreurs = [];

    nom = nom.trim();
    sport = sport.trim();
    coach = coach.trim();

    if (nom === '') {
        erreurs.push("Le nom du programme est obligatoire.");
    } else if (nom.length > 120) {
        erreurs.push("Le nom du programme ne doit pas dépasser 120 caractères.");
    }

    if (sport === '') {
        erreurs.push("Le sport cible est obligatoire.");
    }

    if (dateProgramme === '') {
        erreurs.push("La date du programme est obligatoire.");
    } else if (!/^\d{4}-\d{2}-\d{2}$/.test(dateProgramme)) {
        erreurs.push("La date du programme doit être au format AAAA-MM-JJ.");
    }

    if (coach === '') {
        erreurs.push("Le coach responsable est obligatoire.");
    }

    if (frequence === '' || isNaN(frequence) || parseInt(frequence, 10) < 1 || parseInt(frequence, 10) > 14) {
        erreurs.push("La fréquence doit être un entier entre 1 et 14.");
    }

    if (dureeSemaines === '' || isNaN(dureeSemaines) || parseInt(dureeSemaines, 10) < 1 || parseInt(dureeSemaines, 10) > 104) {
        erreurs.push("La durée doit être un entier entre 1 et 104 semaines.");
    }

    if (maxExercices === '' || isNaN(maxExercices) || parseInt(maxExercices, 10) < 1 || parseInt(maxExercices, 10) > 200) {
        erreurs.push("Le nombre maximal d'exercices doit être un entier entre 1 et 200.");
    }

    var errorDiv = document.getElementById('programFormErrors');
    if (errorDiv) {
        if (erreurs.length > 0) {
            errorDiv.textContent = erreurs.join(' ');
            errorDiv.style.display = 'block';
        } else {
            errorDiv.style.display = 'none';
        }
    }

    return erreurs.length === 0;
}

/**
 * Valider le formulaire Exercice de séance (admin)
 */
function validerFormExercice(form) {
    var nomEx = (document.getElementById('ex_nom') || {}).value || '';
    var duree = (document.getElementById('ex_duree') || {}).value || '';
    var series = (document.getElementById('ex_series') || {}).value || '';
    var repetitions = (document.getElementById('ex_repetitions') || {}).value || '';
    var charge = (document.getElementById('ex_charge') || {}).value || '';
    var distance = (document.getElementById('ex_distance') || {}).value || '';
    var erreurs = [];

    nomEx = nomEx.trim();
    if (nomEx === '') {
        erreurs.push("Le nom de l'exercice est obligatoire.");
    } else if (nomEx.length > 120) {
        erreurs.push("Le nom de l'exercice ne doit pas dépasser 120 caractères.");
    }

    if (duree === '' || isNaN(duree) || parseInt(duree, 10) < 1) {
        erreurs.push("La durée doit être un entier positif (en secondes).");
    }

    if (series !== '' && (isNaN(series) || parseInt(series, 10) < 1)) {
        erreurs.push("Le nombre de séries doit être supérieur ou égal à 1.");
    }

    if (repetitions !== '' && (isNaN(repetitions) || parseInt(repetitions, 10) < 1)) {
        erreurs.push("Le nombre de répétitions doit être supérieur ou égal à 1.");
    }

    if (charge !== '' && (isNaN(charge) || parseFloat(charge) < 0)) {
        erreurs.push("La charge doit être un nombre positif.");
    }

    if (distance !== '' && (isNaN(distance) || parseFloat(distance) < 0)) {
        erreurs.push("La distance doit être un nombre positif.");
    }

    var errorDiv = document.getElementById('exerciseFormErrors');
    if (errorDiv) {
        if (erreurs.length > 0) {
            errorDiv.textContent = erreurs.join(' ');
            errorDiv.style.display = 'block';
        } else {
            errorDiv.style.display = 'none';
        }
    }

    return erreurs.length === 0;
}
