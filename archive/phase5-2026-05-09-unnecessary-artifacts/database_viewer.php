<?php
// Database Viewer for SportFuel
echo "<h1>🗄️ SportFuel Database Viewer</h1>";
echo "<style>body{font-family:Arial;margin:20px;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background:#f2f2f2;}h2{color:#333;margin-top:30px;}</style>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=sportfuel;charset=utf8', 'root', '');

    // Show training sessions
    echo "<h2>📅 Training Sessions (entrainements)</h2>";
    $stmt = $pdo->query("SELECT * FROM entrainements ORDER BY created_at DESC");
    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($trainings) > 0) {
        echo "<table><tr><th>ID</th><th>User ID</th><th>Title</th><th>Date</th><th>Duration</th><th>Status</th><th>Created</th></tr>";
        foreach ($trainings as $training) {
            echo "<tr>";
            echo "<td>{$training['id_entrainement']}</td>";
            echo "<td>{$training['id_utilisateur']}</td>";
            echo "<td>{$training['titre']}</td>";
            echo "<td>{$training['date_entrainement']}</td>";
            echo "<td>{$training['duree_totale']} min</td>";
            echo "<td>{$training['statut']}</td>";
            echo "<td>{$training['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No training sessions found.</p>";
    }

    // Show exercises
    echo "<h2>💪 Exercises (exercices_seance)</h2>";
    $stmt = $pdo->query("SELECT * FROM exercices_seance ORDER BY id_entrainement, ordre");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($exercises) > 0) {
        echo "<table><tr><th>ID</th><th>Training ID</th><th>Exercise Name</th><th>Series</th><th>Reps</th><th>Weight</th><th>Duration</th><th>Distance</th></tr>";
        foreach ($exercises as $exercise) {
            echo "<tr>";
            echo "<td>{$exercise['id_exercice_seance']}</td>";
            echo "<td>{$exercise['id_entrainement']}</td>";
            echo "<td>{$exercise['nom_exercice']}</td>";
            echo "<td>" . ($exercise['series'] ?? '-') . "</td>";
            echo "<td>" . ($exercise['repetitions'] ?? '-') . "</td>";
            echo "<td>" . ($exercise['charge_kg'] ? $exercise['charge_kg'] . 'kg' : '-') . "</td>";
            echo "<td>" . ($exercise['duree_secondes'] ? $exercise['duree_secondes'] . 's' : '-') . "</td>";
            echo "<td>" . ($exercise['distance_km'] ? $exercise['distance_km'] . 'km' : '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No exercises found.</p>";
    }

    // Show summary
    echo "<h2>📊 Summary</h2>";
    echo "<ul>";
    echo "<li><strong>Total Training Sessions:</strong> " . count($trainings) . "</li>";
    echo "<li><strong>Total Exercises:</strong> " . count($exercises) . "</li>";
    echo "<li><strong>Database:</strong> sportfuel</li>";
    echo "<li><strong>Last Updated:</strong> " . date('Y-m-d H:i:s') . "</li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<h2 style='color:red;'>❌ Database Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>