<?php
/**
 * BackOffice — Générateur de repas IA
 * Fonctionnalité avancée intégrée depuis le module Dhya
 * Utilise l'API Groq AI pour générer des suggestions de repas
 */
require_once __DIR__ . '/../../Controller/core/PlanAlimentaireController.php';
require_once __DIR__ . '/../../Controller/core/role_context.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_email']) || !sportfuel_is_backoffice_role()) {
    header('Location: /Esprit-PW-2A19-2026-SportFuel-main/View/auth/connexion.html');
    exit;
}

// Set active sidebar item
$sidebarActive = 'ai';

$generated = null;
$debugInfo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $type_repas = $_POST['type_repas'];
    $kcal = $_POST['kcal'];
    $plan_type = $_POST['plan_type'];
    
    // Check if CURL is available
    if (!function_exists('curl_init')) {
        $generated = "❌ ERREUR: CURL n'est pas activé dans PHP.\n\nActivez l'extension php_curl dans php.ini";
    } else {
        $apiKey = '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';
        $prompt = "Suggère un repas détaillé de type {$type_repas} avec environ {$kcal} kcal pour un régime {$plan_type}. Inclus les ingrédients avec les quantités. Réponds en français.";
        
        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => 300,
            'temperature' => 0.7
        ]));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        $debugInfo = "HTTP Code: {$httpCode}";
        if ($curlError) {
            $debugInfo .= " | CURL Error: {$curlError}";
        }
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['choices'][0]['message']['content'])) {
                $generated = "✅ Généré par IA Groq:\n\n" . $data['choices'][0]['message']['content'];
            } else {
                $generated = "❌ Erreur: Réponse API invalide\n\n" . substr($response, 0, 500);
            }
        } else {
            // Fallback templates
            $templates = [
                'petit_dejeuner' => [
                    'prise_de_masse' => "Flocons d'avoine 100g, banane, whey 30g, beurre de cacahuète 20g\n\nIngrédients:\nFlocons d'avoine 100g\nBanane 1\nWhey protéine 30g\nBeurre de cacahuète 20g",
                    'perte_de_poids' => "Omelette 3 œufs, pain complet 40g, tomate, thé vert\n\nIngrédients:\nŒufs 3\nPain complet 40g\nTomate 1",
                    'maintien' => "Yaourt grec 200g, fruits rouges 80g, miel 10g, amandes 15g\n\nIngrédients:\nYaourt grec 200g\nFruits rouges 80g\nMiel 10g\nAmandes 15g",
                    'endurance' => "Porridge banane-cannelle, beurre de cacahuète 20g\n\nIngrédients:\nFlocons d'avoine 80g\nBanane 1\nBeurre de cacahuète 20g\nCannelle 2g"
                ],
                'dejeuner' => [
                    'prise_de_masse' => "Poulet grillé 220g, riz basmati 120g, brocolis vapeur, huile d'olive\n\nIngrédients:\nPoulet 220g\nRiz basmati 120g\nBrocolis 150g\nHuile d'olive 15ml",
                    'perte_de_poids' => "Dinde 150g, quinoa 80g, courgettes grillées, citron\n\nIngrédients:\nDinde 150g\nQuinoa 80g\nCourgettes 200g\nCitron 1",
                    'maintien' => "Saumon 180g, patate douce 120g, haricots verts\n\nIngrédients:\nSaumon 180g\nPatate douce 120g\nHaricots verts 100g",
                    'endurance' => "Thon 150g, riz complet 120g, avocat, salade mixte\n\nIngrédients:\nThon 150g\nRiz complet 120g\nAvocat 80g\nSalade mixte 100g"
                ],
                'diner' => [
                    'prise_de_masse' => "Bœuf maigre 200g, pâtes complètes 100g, sauce tomate maison\n\nIngrédients:\nBœuf maigre 200g\nPâtes complètes 100g\nTomates 200g\nAil 2 gousses",
                    'perte_de_poids' => "Cabillaud 160g, légumes vapeur, salade verte\n\nIngrédients:\nCabillaud 160g\nCarottes 100g\nHaricots verts 100g\nSalade verte 50g",
                    'maintien' => "Poulet curry 180g, riz basmati 100g, légumes sautés\n\nIngrédients:\nPoulet 180g\nRiz basmati 100g\nPoivrons 100g\nCurry 5g",
                    'endurance' => "Saumon 180g, patate douce 150g, haricots verts, avocat 50g\n\nIngrédients:\nSaumon 180g\nPatate douce 150g\nHaricots verts 100g\nAvocat 50g"
                ],
                'collation' => [
                    'prise_de_masse' => "Smoothie protéiné : banane, whey 30g, lait amande 200ml, beurre de cacahuète 15g\n\nIngrédients:\nBanane 1\nWhey 30g\nLait d'amande 200ml\nBeurre de cacahuète 15g",
                    'perte_de_poids' => "Yaourt grec 150g, fruits rouges 50g\n\nIngrédients:\nYaourt grec 150g\nFruits rouges 50g",
                    'maintien' => "Fruits secs 30g, amandes 20g\n\nIngrédients:\nFruits secs 30g\nAmandes 20g",
                    'endurance' => "Banane, beurre de cacahuète 15g, miel 10g\n\nIngrédients:\nBanane 1\nBeurre de cacahuète 15g\nMiel 10g"
                ]
            ];
            
            $fallback = $templates[$type_repas][$plan_type] ?? "Repas {$type_repas} pour {$plan_type} - {$kcal} kcal";
            $generated = "📋 Mode fallback (template local):\n\n" . $fallback;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur IA — SportFuel</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel-main/public/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../partials/backoffice_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <div>
                    <h1>🤖 Générateur de repas IA</h1>
                    <div class="page-date">Powered by Groq AI</div>
                </div>
            </div>

            <div class="content-area">
                <div class="form-page">
                    <div class="form-card">
                        <div class="form-card-header">
                            <h3>Générer un repas avec l'IA</h3>
                        </div>
                        <form method="POST">
                            <div class="form-card-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="type_repas">Type de repas</label>
                                        <select id="type_repas" name="type_repas" required>
                                            <option value="petit_dejeuner">Petit-déjeuner</option>
                                            <option value="dejeuner">Déjeuner</option>
                                            <option value="diner">Dîner</option>
                                            <option value="collation">Collation</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="kcal">Calories cibles</label>
                                        <input type="number" id="kcal" name="kcal" value="500" required min="100" max="2000">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="plan_type">Type de régime</label>
                                    <select id="plan_type" name="plan_type" required>
                                        <option value="prise_de_masse">Prise de masse</option>
                                        <option value="perte_de_poids">Perte de poids</option>
                                        <option value="maintien">Maintien</option>
                                        <option value="endurance">Endurance</option>
                                    </select>
                                </div>

                                <?php if ($debugInfo): ?>
                                    <div style="padding:10px;background:#f0f0f0;border:1px solid #ddd;border-radius:6px;font-size:12px;margin-bottom:15px;">
                                        <strong>Debug:</strong> <?= htmlspecialchars($debugInfo) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($generated): ?>
                                    <div class="alert alert-success">
                                        <strong>Repas généré :</strong><br>
                                        <pre style="white-space:pre-wrap;font-family:inherit;margin-top:10px;font-size:13.5px;"><?= htmlspecialchars($generated) ?></pre>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-card-footer">
                                <button type="submit" name="generate" class="btn btn-accent">⚡ Générer avec IA</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php include __DIR__ . '/../partials/footer.php'; ?>
        </div>
    </div>
</body>
</html>
