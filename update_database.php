<?php
/**
 * Script de mise à jour de la base de données
 * Ajoute la colonne 'ingredients' à la table Repas
 * 
 * Exécuter une seule fois : http://localhost/Esprit-PW-2A19-2026-SportFuel-main/update_database.php
 */

require_once 'config.php';

$pdo = Config::getConnexion();
$errors = [];
$success = [];

try {
    // 1. Vérifier si la colonne existe déjà
    $checkColumn = $pdo->query("SHOW COLUMNS FROM Repas LIKE 'ingredients'");
    $columnExists = $checkColumn->rowCount() > 0;
    
    if ($columnExists) {
        $success[] = "✅ La colonne 'ingredients' existe déjà dans la table Repas.";
    } else {
        // 2. Ajouter la colonne ingredients
        $sql = "ALTER TABLE Repas ADD COLUMN ingredients TEXT DEFAULT NULL AFTER description";
        $pdo->exec($sql);
        $success[] = "✅ Colonne 'ingredients' ajoutée avec succès à la table Repas.";
    }
    
    // 3. Vérifier la structure de la table
    $structure = $pdo->query("DESCRIBE Repas")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $errors[] = "❌ Erreur lors de la mise à jour : " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de la base de données - SportFuel</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a3c2e, #2d6a4f);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #2d6a4f;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }
        .table-structure {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #e9ecef;
            font-weight: 600;
            color: #495057;
        }
        .highlight {
            background: #fff3cd;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #52b788, #2d6a4f);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
            margin-top: 20px;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            margin-left: 10px;
        }
        .warn {
            background: #fff3cd;
            color: #856404;
            padding: 16px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Mise à jour de la base de données</h1>
        <p class="subtitle">Intégration des métiers avancés - Gestion des ingrédients</p>
        
        <?php if (!empty($success)): ?>
            <?php foreach ($success as $msg): ?>
                <div class="success"><?php echo $msg; ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $msg): ?>
                <div class="error"><?php echo $msg; ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="info">
            <strong>ℹ️ Modifications effectuées :</strong>
            <ul style="margin-top: 10px; margin-left: 20px;">
                <li>Ajout de la colonne <code>ingredients</code> (TEXT) dans la table <code>Repas</code></li>
                <li>Position : après la colonne <code>description</code></li>
                <li>Valeur par défaut : NULL (optionnel)</li>
            </ul>
        </div>
        
        <?php if (isset($structure)): ?>
        <div class="table-structure">
            <h3 style="margin-bottom: 16px; color: #495057;">📋 Structure de la table Repas</h3>
            <table>
                <thead>
                    <tr>
                        <th>Champ</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Clé</th>
                        <th>Défaut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($structure as $column): ?>
                        <tr class="<?php echo $column['Field'] === 'ingredients' ? 'highlight' : ''; ?>">
                            <td><strong><?php echo htmlspecialchars($column['Field']); ?></strong></td>
                            <td><?php echo htmlspecialchars($column['Type']); ?></td>
                            <td><?php echo htmlspecialchars($column['Null']); ?></td>
                            <td><?php echo htmlspecialchars($column['Key']); ?></td>
                            <td><?php echo htmlspecialchars($column['Default'] ?? 'NULL'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px;">
            <a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=back&action=listRepas" class="btn">
                📝 Gérer les repas
            </a>
            <a href="/Esprit-PW-2A19-2026-SportFuel-main/INTEGRATION_METIERS_AVANCES.md" class="btn btn-secondary" target="_blank">
                📚 Documentation
            </a>
        </div>
        
        <div class="warn">
            <strong>⚠️ Important :</strong> Supprimez le fichier <code>update_database.php</code> après cette opération pour des raisons de sécurité.
        </div>
    </div>
</body>
</html>
