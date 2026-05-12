<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportFuel Admin — Gestion des Aliments</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel-main/public/css/style.css">
</head>
<body>
<?php
$sidebarActive = 'aliments';
include __DIR__ . '/../partials/backoffice_sidebar.php';
?>

<!-- ===== MAIN ===== -->
<div class="main-area">

    <div class="page-header">
        <h1>Gestion des Aliments</h1>
        <div class="page-date"><?php echo date('l j F Y'); ?></div>
    </div>

    <div class="content-area">

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Stats dynamiques -->
    <div class="stat-cards">
        <div class="stat-card">
            <div class="value"><?php echo $stats['total']; ?></div>
            <div class="label">Total aliments</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo $stats['nb_bio']; ?></div>
            <div class="trend"><?php echo $stats['total'] > 0 ? round($stats['nb_bio'] * 100 / $stats['total']) . '%' : '0%'; ?></div>
            <div class="label">Produits bio</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo $stats['nb_local']; ?></div>
            <div class="trend"><?php echo $stats['total'] > 0 ? round($stats['nb_local'] * 100 / $stats['total']) . '%' : '0%'; ?></div>
            <div class="label">Produits locaux</div>
        </div>
        <div class="stat-card">
            <div class="value orange"><?php echo $stats['kcal_moyen']; ?></div>
            <div class="trend">Moy. / 100g</div>
            <div class="label">Kcal moyen</div>
        </div>
        <div class="stat-card">
            <div class="value orange"><?php echo $stats['co2_moyen']; ?></div>
            <div class="trend">kg CO₂</div>
            <div class="label">CO₂ moyen</div>
        </div>
    </div>

    <!-- Stats par catégorie -->
    <?php if (!empty($stats['par_categorie'])): ?>
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header"><h3>Répartition par catégorie</h3></div>
        <div style="padding:16px;display:flex;gap:12px;flex-wrap:wrap;">
            <?php foreach ($stats['par_categorie'] as $cat): ?>
                <span class="badge badge-bio"><?php echo htmlspecialchars($cat['categorie']); ?> : <?php echo $cat['nb']; ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recherche & filtres -->
    <form method="GET" action="/Esprit-PW-2A19-2026-SportFuel-main/index.php" class="search-bar" style="margin-bottom:20px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <input type="hidden" name="page" value="aliments">
        <input type="text" name="q" value="<?php echo htmlspecialchars($filtre_q); ?>" placeholder="Rechercher un aliment..." style="flex:1;min-width:200px;padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
        <select name="categorie" style="padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
            <option value="">Toutes les catégories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filtre_categorie === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="bio" style="padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
            <option value="">Bio (tous)</option>
            <option value="1" <?php echo $filtre_bio === '1' ? 'selected' : ''; ?>>Bio uniquement</option>
            <option value="0" <?php echo $filtre_bio === '0' ? 'selected' : ''; ?>>Non bio</option>
        </select>
        <select name="local" style="padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
            <option value="">Local (tous)</option>
            <option value="1" <?php echo $filtre_local === '1' ? 'selected' : ''; ?>>Local uniquement</option>
            <option value="0" <?php echo $filtre_local === '0' ? 'selected' : ''; ?>>Non local</option>
        </select>
        <button class="btn btn-primary" type="submit">🔍 Rechercher</button>
        <a class="btn btn-outline" href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=aliments">Réinitialiser</a>
    </form>

    <!-- Tableau Aliments -->
    <div class="card">
        <div class="card-header">
            <h3>Liste des aliments (<?php echo count($aliments); ?>)</h3>
            <button class="btn btn-primary" onclick="document.getElementById('modalAjout').classList.add('active')">+ Ajouter un aliment</button>
        </div>

        <div class="table-container">
            <table id="tableAliments">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Kcal/100g</th>
                        <th>CO₂ (kg)</th>
                        <th>Prix (TND)</th>
                        <th>Bio</th>
                        <th>Local</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aliments)): ?>
                        <tr><td colspan="9" style="text-align:center;color:#6c757d;">Aucun aliment trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($aliments as $a): ?>
                        <tr>
                            <td>
                                <?php if (!empty($a['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars(cloudinary_thumb($a['image_url'], 80, 80)); ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                <?php else: ?>
                                    <span style="color:#bbb;font-size:24px;">🍽️</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($a['nom']); ?></td>
                            <td><?php echo htmlspecialchars($a['categorie']); ?></td>
                            <td><?php echo $a['kcal_portion']; ?></td>
                            <td><?php echo $a['co2_impact']; ?></td>
                            <td><?php echo number_format((float)$a['prix_unitaire'], 2); ?></td>
                            <td><?php echo $a['est_bio'] ? '<span class="badge badge-bio">Bio</span>' : '—'; ?></td>
                            <td><?php echo $a['est_local'] ? '<span class="badge badge-local">Local</span>' : '—'; ?></td>
                            <td class="actions">
                                <a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=aliments&action=edit&id=<?php echo $a['id_aliment']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                                <form method="POST" action="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=aliments&action=supprimer" style="display:inline;" onsubmit="return confirm('Supprimer cet aliment ?');">
                                    <input type="hidden" name="id" value="<?php echo $a['id_aliment']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>

<!-- ===== Modal: Ajouter Aliment ===== -->
<div class="modal-overlay <?php echo (!empty($error) && $action === 'ajouter') ? 'active' : ''; ?>" id="modalAjout">
    <div class="modal">
        <h3>Ajouter un aliment</h3>
        <form method="POST" action="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=aliments&action=ajouter" enctype="multipart/form-data" onsubmit="return validerFormAliment(this)">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom de l'aliment</label>
                    <input type="text" name="nom" placeholder="Ex: Huile d'olive">
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <input type="text" name="categorie" placeholder="Ex: Fruits, Légumes...">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Calories (kcal / 100g)</label>
                    <input type="text" name="kcal_portion" placeholder="Ex: 884">
                </div>
                <div class="form-group">
                    <label>Impact CO₂ (kg)</label>
                    <input type="text" name="co2_impact" placeholder="Ex: 0.8">
                </div>
            </div>
            <div class="form-group">
                <label>Prix unitaire (TND)</label>
                <input type="text" name="prix_unitaire" placeholder="Ex: 1.50">
            </div>
            <div class="checkbox-inline-row">
                <div class="form-check form-check-chip"><input type="checkbox" name="est_bio" id="checkBio"><label for="checkBio">Produit Bio</label></div>
                <div class="form-check form-check-chip"><input type="checkbox" name="est_local" id="checkLocal"><label for="checkLocal">Produit Local</label></div>
            </div>
            <div class="form-group" style="margin-top:12px;">
                <label>Image (JPEG/PNG/WebP/GIF, max 5 Mo)</label>
                <input type="file" class="sf-file-input" name="image" accept="image/*">
            </div>
            <div id="erreurAjout" style="color:#e63946;margin-top:8px;display:none;"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalAjout').classList.remove('active')">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Modal: Modifier Aliment ===== -->
<?php if ($alimentEdit): ?>
<div class="modal-overlay active" id="modalModif">
    <div class="modal">
        <h3>Modifier l'aliment</h3>
        <form method="POST" action="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=aliments&action=modifier" enctype="multipart/form-data" onsubmit="return validerFormAliment(this)">
            <input type="hidden" name="id" value="<?php echo $alimentEdit['id_aliment']; ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom de l'aliment</label>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($alimentEdit['nom']); ?>">
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <input type="text" name="categorie" value="<?php echo htmlspecialchars($alimentEdit['categorie']); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Calories (kcal / 100g)</label>
                    <input type="text" name="kcal_portion" value="<?php echo $alimentEdit['kcal_portion']; ?>">
                </div>
                <div class="form-group">
                    <label>Impact CO₂ (kg)</label>
                    <input type="text" name="co2_impact" value="<?php echo $alimentEdit['co2_impact']; ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Prix unitaire (TND)</label>
                <input type="text" name="prix_unitaire" value="<?php echo htmlspecialchars($alimentEdit['prix_unitaire']); ?>">
            </div>
            <div class="checkbox-inline-row">
                <div class="form-check form-check-chip"><input type="checkbox" name="est_bio" id="editBio" <?php echo $alimentEdit['est_bio'] ? 'checked' : ''; ?>><label for="editBio">Produit Bio</label></div>
                <div class="form-check form-check-chip"><input type="checkbox" name="est_local" id="editLocal" <?php echo $alimentEdit['est_local'] ? 'checked' : ''; ?>><label for="editLocal">Produit Local</label></div>
            </div>
            <div class="form-group" style="margin-top:12px;">
                <label>Image</label>
                <?php if (!empty($alimentEdit['image_url'])): ?>
                    <div style="margin-bottom:8px;"><img src="<?php echo htmlspecialchars(cloudinary_thumb($alimentEdit['image_url'], 120, 120)); ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:6px;"></div>
                <?php endif; ?>
                <input type="file" class="sf-file-input" name="image" accept="image/*">
                <small style="color:#6c757d;display:block;margin-top:4px;">Laisser vide pour conserver l'image actuelle.</small>
            </div>
            <div id="erreurModif" style="color:#e63946;margin-top:8px;display:none;"></div>
            <div class="modal-actions">
                <a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=aliments" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="/Esprit-PW-2A19-2026-SportFuel-main/public/js/validation.js"></script>

</body>
</html>
