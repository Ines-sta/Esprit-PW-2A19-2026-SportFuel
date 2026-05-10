<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportFuel — Catalogue d'Aliments</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2526-SportFuel/public/css/style.css">
</head>
<body>

<?php
$currentUserName = isset($_SESSION['user_nom'])
    ? (string)$_SESSION['user_nom']
    : (isset($_SESSION['user_email']) ? explode('@', (string)$_SESSION['user_email'])[0] : 'Sportif');

$navbarActive = 'aliments';
include __DIR__ . '/../partials/navbar.php';
?>

<!-- ===== MAIN ===== -->
<div class="main-content">

    <div class="welcome-banner">
        <div>
            <p class="greeting">Catalogue d'aliments</p>
            <h2>Découvrez nos aliments bio &amp; locaux</h2>
            <p class="sub">Connecté en tant que <?php echo htmlspecialchars($currentUserName); ?>. Produits tunisiens sélectionnés pour vos performances sportives.</p>
        </div>
    </div>

    <!-- Stats dynamiques -->
    <div class="stat-cards">
        <div class="stat-card">
            <div class="value"><?php echo $stats['total']; ?></div>
            <div class="label">Aliments disponibles</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo $stats['nb_bio']; ?></div>
            <div class="label">Produits bio</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo $stats['nb_local']; ?></div>
            <div class="label">Produits locaux</div>
        </div>
        <div class="stat-card">
            <div class="value orange"><?php echo count($stats['par_categorie']); ?></div>
            <div class="label">Catégories</div>
        </div>
        <div class="stat-card">
            <div class="value orange"><?php echo $stats['kcal_moyen']; ?></div>
            <div class="trend">kcal / 100g</div>
            <div class="label">Moyenne énergétique</div>
        </div>
    </div>

    <!-- Recherche & filtres -->
    <form method="GET" action="/Esprit-PW-2A19-2526-SportFuel/index.php" class="search-bar">
        <input type="hidden" name="page" value="aliments">
        <input type="text" name="q" value="<?php echo htmlspecialchars($filtre_q); ?>" placeholder="Rechercher un aliment...">
        <select name="categorie">
            <option value="">Toutes les catégories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filtre_categorie === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="bio">
            <option value="">Bio (tous)</option>
            <option value="1" <?php echo $filtre_bio === '1' ? 'selected' : ''; ?>>Bio uniquement</option>
            <option value="0" <?php echo $filtre_bio === '0' ? 'selected' : ''; ?>>Non bio</option>
        </select>
        <select name="local">
            <option value="">Local (tous)</option>
            <option value="1" <?php echo $filtre_local === '1' ? 'selected' : ''; ?>>Local uniquement</option>
            <option value="0" <?php echo $filtre_local === '0' ? 'selected' : ''; ?>>Non local</option>
        </select>
        <button class="btn btn-primary" type="submit">🔍 Rechercher</button>
        <a class="btn btn-outline" href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=aliments">Réinitialiser</a>
    </form>

    <!-- Food Grid -->
    <?php
    // Name-based image mapping (keyword → image file in assets/images/)
    $imageMap = [
        'couscous' => ['file' => 'Couscous-Complet.png', 'alt' => 'Couscous complet'],
        'datte'    => ['file' => 'Dattes.png',           'alt' => 'Dattes'],
        'harissa'  => ['file' => 'Harissa.jpg',          'alt' => 'Harissa'],
        'huile'    => ['file' => 'huile.jpg',            'alt' => 'Huile d\'olive'],
        'olive'    => ['file' => 'huile.jpg',            'alt' => 'Huile d\'olive'],
        'poivron'  => ['file' => 'poivron.webp',         'alt' => 'Poivron'],
        'thon'     => ['file' => 'thon.png',             'alt' => 'Thon'],
        'yaourt'   => ['file' => 'yaourt.png',           'alt' => 'Yaourt'],
        'yogourt'  => ['file' => 'yaourt.png',           'alt' => 'Yaourt'],
    ];

    // Fallback emojis by category
    $emojis = [
        'Fruits'              => '🍎',
        'Légumes'             => '🥬',
        'Protéines'           => '🥚',
        'Céréales & Féculents'=> '🌾',
        'Produits laitiers'   => '🥛',
        'Huiles & Graisses'   => '🫒',
        'Fruits secs'         => '🌰'
    ];

    function getAlimentImage($nom, $imageMap) {
        $nomLower = mb_strtolower($nom, 'UTF-8');
        foreach ($imageMap as $keyword => $img) {
            if (strpos($nomLower, $keyword) !== false) {
                return $img;
            }
        }
        return null;
    }
    ?>
    <p class="result-count"><?php echo count($aliments); ?> aliment(s) trouvé(s).</p>
    <div class="food-grid" id="foodGrid">
        <?php if (empty($aliments)): ?>
            <p class="empty-hint">Aucun aliment ne correspond à vos critères.</p>
        <?php else: ?>
            <?php foreach ($aliments as $a):
                $img = getAlimentImage($a['nom'], $imageMap);
            ?>
            <div class="food-card" data-categorie="<?php echo htmlspecialchars($a['categorie']); ?>" data-bio="<?php echo $a['est_bio']; ?>" data-local="<?php echo $a['est_local']; ?>">
                <div class="food-card-img">
                    <?php if (!empty($a['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars(cloudinary_thumb($a['image_url'], 400, 400)); ?>" alt="<?php echo htmlspecialchars($a['nom']); ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php elseif ($img): ?>
                        <img src="/Esprit-PW-2A19-2526-SportFuel/public/images/<?php echo $img['file']; ?>" alt="<?php echo htmlspecialchars($img['alt']); ?>" style="width:100%;height:100%;object-fit:contain;">
                    <?php else: ?>
                        <?php echo $emojis[$a['categorie']] ?? '🍽️'; ?>
                    <?php endif; ?>
                </div>
                <div class="food-card-body">
                    <h4><?php echo htmlspecialchars($a['nom']); ?></h4>
                    <p class="category"><?php echo htmlspecialchars($a['categorie']); ?></p>
                    <div style="display:flex;gap:6px;margin-bottom:8px;">
                        <?php if ($a['est_bio']): ?><span class="badge badge-bio">Bio</span><?php endif; ?>
                        <?php if ($a['est_local']): ?><span class="badge badge-local">Local</span><?php endif; ?>
                    </div>
                    <div class="food-card-meta">
                        <span class="kcal"><?php echo $a['kcal_portion']; ?> kcal / 100g</span>
                        <span class="co2"><?php echo $a['co2_impact']; ?> kg CO₂</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<div class="footer">
    &copy; 2026 SportFuel — Nutrition intelligente pour sportifs | Projet Web 2A Esprit
</div>

</body>
</html>
