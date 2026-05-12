<?php
/**
 * BackOffice — Liste de courses générée automatiquement
 * Fonctionnalité avancée intégrée depuis le module Dhya
 */
require_once __DIR__ . '/../../Controller/core/PlanAlimentaireController.php';
require_once __DIR__ . '/../../Controller/core/role_context.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_email'])) {
    header('Location: /Esprit-PW-2A19-2026-SportFuel-main/View/auth/connexion.html');
    exit;
}

// Set active sidebar item
$sidebarActive = 'shopping';

$planController = new PlanAlimentaireController();
$plans = $planController->listPlans();

$shoppingList = [];
$selectedPlan = null;

if (isset($_GET['id_plan'])) {
    $id_plan = (int)$_GET['id_plan'];
    $data = $planController->getPlanWithRepas($id_plan);
    $selectedPlan = $data['plan'];
    $repas = $data['repas'];
    
    // Parse ingredients
    $categories = [
        'Protéines' => ['poulet', 'dinde', 'boeuf', 'saumon', 'thon', 'cabillaud', 'oeuf', 'whey'],
        'Féculents' => ['riz', 'pates', 'pain', 'quinoa', 'avoine', 'patate'],
        'Légumes' => ['brocoli', 'courgette', 'haricot', 'salade', 'tomate', 'carotte', 'epinard', 'poivron'],
        'Fruits' => ['banane', 'myrtille', 'fruit', 'avocat'],
        'Produits laitiers' => ['yaourt', 'lait'],
        'Autres' => []
    ];
    
    foreach ($repas as $r) {
        if (empty($r['ingredients'])) continue;
        $items = explode(',', $r['ingredients']);
        foreach ($items as $item) {
            $item = trim($item);
            if (empty($item)) continue;
            
            $categorized = false;
            foreach ($categories as $cat => $keywords) {
                foreach ($keywords as $kw) {
                    if (stripos($item, $kw) !== false) {
                        $shoppingList[$cat][] = $item;
                        $categorized = true;
                        break 2;
                    }
                }
            }
            if (!$categorized) {
                $shoppingList['Autres'][] = $item;
            }
        }
    }
    
    // Remove duplicates
    foreach ($shoppingList as $cat => $items) {
        $shoppingList[$cat] = array_unique($items);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste de courses — SportFuel</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel-main/public/css/style.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php 
        if (sportfuel_is_backoffice_role()) {
            include __DIR__ . '/../partials/backoffice_sidebar.php';
        } else {
            include __DIR__ . '/../partials/sidebar.php';
        }
        ?>
        
        <div class="main-content">
            <div class="page-header no-print">
                <div>
                    <h1>🛒 Liste de courses</h1>
                    <div class="page-date">Générée automatiquement depuis vos plans</div>
                </div>
            </div>

            <div class="content-area">
                <div class="card no-print" style="max-width:800px;">
                    <div class="card-header">
                        <h3>Sélectionner un plan</h3>
                    </div>
                    <div style="padding:20px;">
                        <form method="GET">
                            <input type="hidden" name="page" value="plans">
                            <input type="hidden" name="action" value="shopping_list">
                            <div class="form-group">
                                <select name="id_plan" class="form-control" onchange="this.form.submit()" style="width:100%;height:40px;padding:0 12px;border:1px solid #ddd;border-radius:8px;">
                                    <option value="">-- Choisir un plan --</option>
                                    <?php foreach ($plans as $p): ?>
                                        <option value="<?= $p->getIdPlan() ?>" <?= isset($id_plan) && $id_plan == $p->getIdPlan() ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p->getNom()) ?> (Semaine <?= $p->getSemaine() ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($selectedPlan && !empty($shoppingList)): ?>
                    <div class="card" style="max-width:800px;margin-top:20px;">
                        <div class="card-header">
                            <h3>Liste pour : <?= htmlspecialchars($selectedPlan->getNom()) ?></h3>
                            <button onclick="window.print()" class="btn btn-outline btn-sm no-print">🖨️ Imprimer</button>
                        </div>
                        <div style="padding:24px;">
                            <?php foreach ($shoppingList as $category => $items): ?>
                                <?php if (empty($items)) continue; ?>
                                <div style="margin-bottom:24px;">
                                    <h4 style="font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;color:#52b788;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.5px;">
                                        <?= $category ?>
                                    </h4>
                                    <ul style="list-style:none;padding:0;">
                                        <?php foreach ($items as $item): ?>
                                            <li style="padding:8px 0;border-bottom:1px solid #e9ecef;display:flex;align-items:center;gap:10px;">
                                                <input type="checkbox" style="width:18px;height:18px;cursor:pointer;" class="no-print">
                                                <span style="font-size:13.5px;"><?= htmlspecialchars($item) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php elseif ($selectedPlan): ?>
                    <div class="empty-state">Aucun ingrédient trouvé pour ce plan.</div>
                <?php endif; ?>
            </div>
            
            <?php include __DIR__ . '/../partials/footer.php'; ?>
        </div>
    </div>
</body>
</html>
