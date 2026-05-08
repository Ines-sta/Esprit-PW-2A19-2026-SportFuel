<?php

require_once 'Entrainement.php';

class ExerciceSeance {
    private $db;
    private $database;

    public function __construct($database) {
        $this->database = $database;
        $this->db = $database->getPDO();
    }

    // Récupérer tous les exercices d'une séance
    public function getAllByEntrainement($id_entrainement) {
        $sql = "SELECT * FROM exercices_seance WHERE id_entrainement = ? ORDER BY ordre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_entrainement]);
        return $stmt->fetchAll();
    }

    // Récupérer un exercice par ID
    public function getById($id) {
        $sql = "SELECT * FROM exercices_seance WHERE id_exercice_seance = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Créer un nouvel exercice
    public function create($data) {
        // Validation
        if (empty($data['id_entrainement']) || empty($data['nom_exercice']) || (empty($data['duree']) && empty($data['duree_secondes']))) {
            throw new Exception('Les champs requis sont manquants');
        }

        // Vérifier que l'entraînement existe
        $entrainementModel = new Entrainement($this->database);
        if (!$entrainementModel->getById($data['id_entrainement'])) {
            throw new Exception('Entraînement non trouvé');
        }

        $duree = $data['duree_secondes'] ?? $data['duree'] ?? null;
        if (!is_numeric($duree) || $duree < 1) {
            throw new Exception('Durée invalide');
        }

        if (!empty($data['repetitions']) && (!is_numeric($data['repetitions']) || $data['repetitions'] < 1)) {
            throw new Exception('Nombre de répétitions invalide');
        }

        if (!empty($data['poids']) && (!is_numeric($data['poids']) || $data['poids'] < 0)) {
            throw new Exception('Poids invalide');
        }

        $sql = "INSERT INTO exercices_seance (id_entrainement, nom_exercice, duree_secondes, series, repetitions, charge_kg, distance_km)\n                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['id_entrainement'],
            $data['nom_exercice'],
            $duree,
            $data['series'] ?? null,
            $data['repetitions'] ?? null,
            $data['charge_kg'] ?? $data['poids'] ?? null,
            $data['distance_km'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    // Mettre à jour un exercice
    public function update($id_exercice, $data) {
        $allowedFields = ['nom_exercice', 'duree_secondes', 'series', 'repetitions', 'charge_kg', 'distance_km'];
        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                if (in_array($key, ['duree_secondes', 'series', 'repetitions']) && !is_numeric($value) && $value !== null) {
                    throw new Exception('Valeur numérique invalide pour ' . $key);
                }
                if ($key === 'charge_kg' && !is_numeric($value) && $value !== null && $value < 0) {
                    throw new Exception('Charge invalide');
                }
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) {
            throw new Exception('Aucun champ à mettre à jour');
        }

        $params[] = $id_exercice;
        $sql = "UPDATE exercices_seance SET " . implode(", ", $updates) . " WHERE id_exercice_seance = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    // Supprimer un exercice
    public function delete($id_exercice) {
        $sql = "DELETE FROM exercices_seance WHERE id_exercice_seance = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_exercice]);
        return $stmt->rowCount();
    }
}

?>