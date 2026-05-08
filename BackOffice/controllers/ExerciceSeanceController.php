<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/ExerciceSeance.php';

class ExerciceSeanceController {
    private $model;

    public function __construct() {
        $database = new Database();
        $this->model = new ExerciceSeance($database);
    }

    // Gérer les requêtes GET
    public function get($id_entrainement = null, $id_exercice = null) {
        try {
            if ($id_exercice) {
                $result = $this->model->getById($id_exercice);
                if (!$result) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Exercice non trouvé']);
                    return;
                }
            } elseif ($id_entrainement) {
                $result = $this->model->getAllByEntrainement($id_entrainement);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Paramètre manquant']);
                return;
            }

            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Gérer les requêtes POST
    public function post($data) {
        try {
            $id = $this->model->create($data);
            http_response_code(201);
            echo json_encode(['id' => $id, 'message' => 'Exercice créé avec succès']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Gérer les requêtes PUT
    public function put($id_exercice, $data) {
        try {
            $affected = $this->model->update($id_exercice, $data);
            if ($affected === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Exercice non trouvé']);
                return;
            }
            echo json_encode(['message' => 'Exercice mis à jour avec succès']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Gérer les requêtes DELETE
    public function delete($id_exercice) {
        try {
            $affected = $this->model->delete($id_exercice);
            if ($affected === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Exercice non trouvé']);
                return;
            }
            echo json_encode(['message' => 'Exercice supprimé avec succès']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

?>