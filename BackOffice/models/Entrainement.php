<?php

class Entrainement {
    private $db;

    public function __construct($database) {
        $this->db = $database->getPDO();
    }

    // Récupérer tous les entraînements d'un utilisateur
    public function getAll($id_utilisateur) {
        $sql = "SELECT * FROM entrainements WHERE id_utilisateur = ? ORDER BY date_entrainement DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_utilisateur]);
        return $stmt->fetchAll();
    }

    // Récupérer un entraînement par ID
    public function getById($id) {
        $sql = "SELECT * FROM entrainements WHERE id_entrainement = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Créer un nouvel entraînement
    public function create($data) {
        // Validation
        if (empty($data['id_utilisateur']) || empty($data['titre']) || empty($data['date_entrainement'])) {
            throw new Exception('Les champs requis sont manquants');
        }

        if (!$this->isValidDate($data['date_entrainement'])) {
            throw new Exception('Format de date invalide');
        }

        if (!empty($data['duree_totale']) && (!is_numeric($data['duree_totale']) || $data['duree_totale'] < 1)) {
            throw new Exception('Durée invalide');
        }

        $sql = "INSERT INTO entrainements (id_utilisateur, titre, date_entrainement, duree_totale, notes_globales, statut)\n                VALUES (?, ?, ?, ?, ?, 'En attente')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['id_utilisateur'],
            $data['titre'],
            $data['date_entrainement'],
            $data['duree_totale'] ?? null,
            $data['notes_globales'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    // Mettre à jour un entraînement
    public function update($id_entrainement, $data) {
        $allowedFields = ['titre', 'date_entrainement', 'duree_totale', 'notes_globales', 'statut'];
        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                if ($key === 'date_entrainement' && !$this->isValidDate($value)) {
                    throw new Exception('Format de date invalide');
                }
                if ($key === 'duree_totale' && !is_numeric($value) && $value !== null) {
                    throw new Exception('Durée invalide');
                }
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) {
            throw new Exception('Aucun champ à mettre à jour');
        }

        $params[] = $id_entrainement;
        $sql = "UPDATE entrainements SET " . implode(", ", $updates) . " WHERE id_entrainement = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    // Supprimer un entraînement
    public function delete($id_entrainement) {
        $sql = "DELETE FROM entrainements WHERE id_entrainement = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_entrainement]);
        return $stmt->rowCount();
    }

    // Vérifier si la date est valide
    private function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
?>