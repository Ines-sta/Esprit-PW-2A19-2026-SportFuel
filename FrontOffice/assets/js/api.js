// Configuration API - MVC
const API_BASE_URL = 'http://localhost/Esprit-PW-2A19-2526-SportFuel/public/api';
const USER_ID = 1; // À remplacer par l'ID de l'utilisateur connecté

// ====== ENTRAINEMENTS ======

// Récupérer tous les entraînements
async function getAllEntrainements() {
    try {
        const response = await fetch(`${API_BASE_URL}/entrainements.php?id_utilisateur=${USER_ID}`);
        const data = await response.json();
        if (response.ok) {
            return data;
        } else {
            console.error('Erreur:', data.error);
            return [];
        }
    } catch (error) {
        console.error('Erreur de connexion API:', error);
        return [];
    }
}

// Récupérer un entraînement par ID
async function getEntrainementById(id) {
    try {
        const response = await fetch(`${API_BASE_URL}/entrainements.php?id_utilisateur=${USER_ID}&id_entrainement=${id}`);
        const data = await response.json();
        if (response.ok) {
            return data;
        } else {
            console.error('Erreur:', data.error);
            return null;
        }
    } catch (error) {
        console.error('Erreur de connexion API:', error);
        return null;
    }
}

// Créer un nouvel entraînement
async function createEntrainement(titre, date_entrainement, duree_totale = null, notes_globales = null, calories_estimees = null) {
    try {
        const response = await fetch(`${API_BASE_URL}/entrainements.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_utilisateur: USER_ID,
                titre: titre,
                date_entrainement: date_entrainement,
                duree_totale: duree_totale,
                notes_globales: notes_globales,
                calories_estimees: calories_estimees
            })
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur de connexion API:', error);
        return { error: 'Erreur de connexion' };
    }
}

// Mettre à jour un entraînement
async function updateEntrainement(id_entrainement, titre = null, date_entrainement = null, duree_totale = null, notes_globales = null, statut = null) {
    try {
        const updateData = {};
        if (titre !== null) updateData.titre = titre;
        if (date_entrainement !== null) updateData.date_entrainement = date_entrainement;
        if (duree_totale !== null) updateData.duree_totale = duree_totale;
        if (notes_globales !== null) updateData.notes_globales = notes_globales;
        if (statut !== null) updateData.statut = statut;

        const response = await fetch(`${API_BASE_URL}/entrainements.php?id_entrainement=${id_entrainement}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(updateData)
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur de connexion API:', error);
        return { error: 'Erreur de connexion' };
    }
}

// Supprimer un entraînement
async function deleteEntrainement(id_entrainement) {
    try {
        const response = await fetch(`${API_BASE_URL}/entrainements.php?id_entrainement=${id_entrainement}`, {
            method: 'DELETE'
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur de connexion API:', error);
        return { error: 'Erreur de connexion' };
    }
}

// ====== EXERCICES ======

// Récupérer les exercices d'un entraînement
async function getExercicesByEntrainement(id_entrainement) {
    try {
        const response = await fetch(`${API_BASE_URL}/exercices.php?id_entrainement=${id_entrainement}`);
        const data = await response.json();
        if (response.ok) {
            return data;
        } else {
            console.error('Erreur:', data.error);
            return [];
        }
    } catch (error) {
        console.error('Erreur de connexion API:', error);
        return [];
    }
}

// Récupérer un exercice par ID
async function getExerciceById(id) {
    try {
        const response = await fetch(`${API_BASE_URL}/exercices.php?id_exercice=${id}`);
        const data = await response.json();
        if (response.ok) {
            return data;
        } else {
            console.error('Erreur:', data.error);
            return null;
        }
    } catch (error) {
        console.error('Erreur de connexion API:', error);
        return null;
    }
}

// Créer un nouvel exercice
async function createExercice(id_entrainement, nom_exercice, duree, repetitions = null, poids = null, notes = null, ordre = 0) {
    try {
        const response = await fetch(`${API_BASE_URL}/exercices.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_entrainement: id_entrainement,
                nom_exercice: nom_exercice,
                duree: duree,
                repetitions: repetitions,
                poids: poids,
                notes: notes,
                ordre: ordre
            })
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur de connexion API:', error);
        return { error: 'Erreur de connexion' };
    }
}

// Mettre à jour un exercice
async function updateExercice(id_exercice, nom_exercice = null, duree = null, repetitions = null, poids = null, notes = null, ordre = null) {
    try {
        const updateData = {};
        if (nom_exercice !== null) updateData.nom_exercice = nom_exercice;
        if (duree !== null) updateData.duree = duree;
        if (repetitions !== null) updateData.repetitions = repetitions;
        if (poids !== null) updateData.poids = poids;
        if (notes !== null) updateData.notes = notes;
        if (ordre !== null) updateData.ordre = ordre;

        const response = await fetch(`${API_BASE_URL}/exercices.php?id_exercice=${id_exercice}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(updateData)
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur de connexion API:', error);
        return { error: 'Erreur de connexion' };
    }
}

// Supprimer un exercice
async function deleteExercice(id_exercice) {
    try {
        const response = await fetch(`${API_BASE_URL}/exercices.php?id_exercice=${id_exercice}`, {
            method: 'DELETE'
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur de connexion API:', error);
        return { error: 'Erreur de connexion' };
    }
}
