// Validation JavaScript - Gestion centralisée de la validation des formulaires

class FormValidator {
    constructor() {
        this.errors = [];
    }

    // Réinitialiser les erreurs
    clearErrors() {
        this.errors = [];
    }

    // Ajouter une erreur
    addError(fieldName, message) {
        this.errors.push({ field: fieldName, message: message });
    }

    // Récupérer les erreurs
    getErrors() {
        return this.errors;
    }

    // Vérifier si le champ est vide
    isRequired(value, fieldName) {
        if (!value || value.toString().trim() === '') {
            this.addError(fieldName, `${fieldName} est requis`);
            return false;
        }
        return true;
    }

    // Valider une date au format YYYY-MM-DD
    isValidDate(dateString, fieldName) {
        const regex = /^\d{4}-\d{2}-\d{2}$/;
        if (!regex.test(dateString)) {
            this.addError(fieldName, `Format de date invalide. Utilisez YYYY-MM-DD`);
            return false;
        }

        // Vérifier que la date est valide
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            this.addError(fieldName, `La date n'est pas valide`);
            return false;
        }

        return true;
    }

    // Valider un nombre positif
    isPositiveNumber(value, fieldName) {
        const num = parseFloat(value);
        if (isNaN(num) || num <= 0) {
            this.addError(fieldName, `${fieldName} doit être un nombre positif`);
            return false;
        }
        return true;
    }

    // Valider un nombre positif ou zéro
    isNonNegativeNumber(value, fieldName) {
        const num = parseFloat(value);
        if (isNaN(num) || num < 0) {
            this.addError(fieldName, `${fieldName} doit être un nombre positif ou zéro`);
            return false;
        }
        return true;
    }

    // Valider une longueur minimale
    hasMinLength(value, minLength, fieldName) {
        if (value.toString().length < minLength) {
            this.addError(fieldName, `${fieldName} doit contenir au moins ${minLength} caractères`);
            return false;
        }
        return true;
    }

    // Valider une longueur maximale
    hasMaxLength(value, maxLength, fieldName) {
        if (value.toString().length > maxLength) {
            this.addError(fieldName, `${fieldName} ne doit pas dépasser ${maxLength} caractères`);
            return false;
        }
        return true;
    }

    // Valider le format d'email
    isValidEmail(email, fieldName) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regex.test(email)) {
            this.addError(fieldName, `${fieldName} n'est pas valide`);
            return false;
        }
        return true;
    }

    // Valider un format numérique avec décimales
    isDecimal(value, fieldName) {
        const num = parseFloat(value);
        if (isNaN(num)) {
            this.addError(fieldName, `${fieldName} doit être un nombre`);
            return false;
        }
        return true;
    }

    // Afficher les erreurs sur la page
    displayErrors(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = '';

        if (this.errors.length === 0) {
            container.style.display = 'none';
            return;
        }

        const errorHtml = `
            <div class="alert alert-danger">
                <strong>Erreurs détectées:</strong>
                <ul>
                    ${this.errors.map(e => `<li>${e.message}</li>`).join('')}
                </ul>
            </div>
        `;

        container.innerHTML = errorHtml;
        container.style.display = 'block';
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Vérifier s'il y a des erreurs
    hasErrors() {
        return this.errors.length > 0;
    }
}

// Fonction utilitaire pour valider un formulaire d'entraînement
function validateTrainingForm(formData) {
    const validator = new FormValidator();

    // Validation du type d'entraînement
    validator.isRequired(formData.titre, 'Type d\'entraînement');

    // Validation de la date
    if (validator.isRequired(formData.date_entrainement, 'Date')) {
        validator.isValidDate(formData.date_entrainement, 'Date');
    }

    // Validation de la durée
    if (validator.isRequired(formData.duree_totale, 'Durée')) {
        validator.isPositiveNumber(formData.duree_totale, 'Durée (minutes)');
    }

    // Validation des calories
    if (validator.isRequired(formData.calories_estimees, 'Calories')) {
        validator.isNonNegativeNumber(formData.calories_estimees, 'Calories estimées');
    }

    // Validation optionnelle de la distance
    if (formData.distance && formData.distance.trim() !== '') {
        validator.isDecimal(formData.distance, 'Distance');
    }

    // Validation optionnelle des notes
    if (formData.notes && formData.notes.length > 500) {
        validator.hasMaxLength(formData.notes, 500, 'Notes');
    }

    return validator;
}

// Fonction pour afficher les messages d'erreur avec style
function showFormError(message) {
    const messageContainer = document.getElementById('messageContainer');
    if (messageContainer) {
        messageContainer.innerHTML = `
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <strong>Erreur:</strong> ${message}
            </div>
        `;
        messageContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Fonction pour afficher les messages de succès
function showFormSuccess(message) {
    const messageContainer = document.getElementById('messageContainer');
    if (messageContainer) {
        messageContainer.innerHTML = `
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <strong>Succès:</strong> ${message}
            </div>
        `;
        messageContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Réinitialiser le formulaire après succès
        setTimeout(() => {
            const form = document.getElementById('trainingForm');
            if (form) form.reset();
        }, 1000);
    }
}

// Classe pour gérer les appels de formulaire
class FormSubmissionHandler {
    constructor(formId, submitUrl) {
        this.formId = formId;
        this.submitUrl = submitUrl;
        this.form = document.getElementById(formId);
        
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    handleSubmit(e) {
        e.preventDefault();

        const formData = new FormData(this.form);
        const data = Object.fromEntries(formData);

        // Valider les données
        const validator = validateTrainingForm(data);

        if (validator.hasErrors()) {
            validator.displayErrors('messageContainer');
            return;
        }

        // Soumettre le formulaire
        this.submitForm(data);
    }

    async submitForm(data) {
        try {
            const formData = new FormData();
            for (const key in data) {
                formData.append(key, data[key]);
            }

            const response = await fetch(this.submitUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showFormSuccess(result.message);
                // Recharger les données si nécessaire
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                if (result.errors && Array.isArray(result.errors)) {
                    showFormError(result.errors.join('\n'));
                } else {
                    showFormError(result.error || 'Une erreur est survenue');
                }
            }
        } catch (error) {
            showFormError('Erreur de connexion: ' + error.message);
        }
    }
}
