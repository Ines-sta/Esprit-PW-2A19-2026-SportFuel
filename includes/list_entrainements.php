<?php
require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $database = new Database();
    $pdo = $database->getPDO();

    $programmesOnly = isset($_GET['programmes']) && $_GET['programmes'] === '1';

    $sql = "SELECT e.id_entrainement,
                   e.id_utilisateur,
                   e.titre,
                   e.date_entrainement,
                   e.duree_totale,
                   e.notes_globales,
                   e.statut,
                   COUNT(es.id_exercice_seance) AS nb_exercices
            FROM entrainements e
            LEFT JOIN exercices_seance es ON es.id_entrainement = e.id_entrainement ";
    if ($programmesOnly) {
        $sql .= "WHERE e.notes_globales LIKE '__PROGRAMME__|%' ";
    }
    $sql .= "GROUP BY e.id_entrainement, e.id_utilisateur, e.titre, e.date_entrainement, e.duree_totale, e.notes_globales, e.statut
             ORDER BY e.date_entrainement DESC, e.id_entrainement DESC";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $rows
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
