// ============================================
// SportFuel — Validation JavaScript côté client
// Pas de validation HTML5 (contrainte du projet)
// ============================================

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

    return afficherErreurs(form, erreurs);
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
    } else if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
        erreurs.push("La date doit être au format AAAA-MM-JJ.");
    }

    if (statut === '') {
        erreurs.push("Le statut est obligatoire.");
    }

    return afficherErreurs(form, erreurs);
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
