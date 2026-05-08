<?php
require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Parse notes __PROGRAMME__|Libelle|Niveau: …|Fréquence: …|Durée: …|Coach: …
 */
function parse_programme_notes($notes) {
    $out = [
        'libelle_programme' => '',
        'niveau' => '',
        'frequence' => '',
        'duree_semaines' => '',
        'coach' => ''
    ];
    if ($notes === null || $notes === '' || strpos($notes, '__PROGRAMME__|') !== 0) {
        return $out;
    }
    $rest = substr($notes, strlen('__PROGRAMME__|'));
    $parts = array_map('trim', explode('|', $rest));
    $out['libelle_programme'] = isset($parts[0]) ? $parts[0] : '';

    for ($i = 1; $i < count($parts); $i++) {
        $p = $parts[$i];
        if (preg_match('/^Niveau:\s*(.+)$/iu', $p, $m)) {
            $out['niveau'] = trim($m[1]);
        } elseif (preg_match('/^Fréquence:\s*(.+)$/iu', $p, $m)) {
            $out['frequence'] = trim($m[1]);
        } elseif (preg_match('/^Durée:\s*(.+)$/iu', $p, $m)) {
            $out['duree_semaines'] = trim($m[1]);
        } elseif (preg_match('/^Coach:\s*(.+)$/iu', $p, $m)) {
            $out['coach'] = trim($m[1]);
        }
    }
    return $out;
}

try {
    $database = new Database();
    $pdo = $database->getPDO();

    $sql = "SELECT id_entrainement, id_utilisateur, titre, date_entrainement, duree_totale, notes_globales, statut
            FROM entrainements
            WHERE notes_globales LIKE '__PROGRAMME__|%'
            ORDER BY date_entrainement DESC, id_entrainement DESC";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach ($rows as $row) {
        $meta = parse_programme_notes($row['notes_globales'] ?? '');
        $out[] = [
            'id_entrainement' => (int) $row['id_entrainement'],
            'id_utilisateur' => $row['id_utilisateur'],
            'titre' => $row['titre'],
            'date_entrainement' => $row['date_entrainement'],
            'duree_totale' => $row['duree_totale'],
            'statut' => $row['statut'],
            'libelle_programme' => $meta['libelle_programme'],
            'niveau' => $meta['niveau'],
            'frequence' => $meta['frequence'],
            'duree_semaines' => $meta['duree_semaines'],
            'coach' => $meta['coach']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $out
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
