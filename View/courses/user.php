<?php
// Vue FO: Listes de courses
// Reçoit : $courses, $stats, $courseDetail, $filtre_*, $success,
// $currentUserName.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportFuel — Mes Listes de Courses</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel/public/css/style.css">
</head>
<body>

<?php
$navbarActive = 'courses';
include __DIR__ . '/../partials/navbar.php';
?>

<!-- ===== MAIN ===== -->
<div class="main-content">

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="welcome-banner">
        <div>
            <p class="greeting">Listes de courses</p>
            <h2>Vos listes de courses</h2>
            <p class="sub">Connecté en tant que <?php echo htmlspecialchars($currentUserName); ?>. Suivez vos achats et vos apports énergétiques.</p>
        </div>
    </div>

    <?php if ($courseDetail):
        $totalKcal = Course::kcalTotal($courseDetail['articles']);
    ?>
    <!-- ===== VUE DÉTAIL ===== -->
    <?php
    $emojis = [
        'Fruits' => '🍎', 'Légumes' => '🥬', 'Protéines' => '🥩',
        'Céréales' => '🌾', 'Céréales & Féculents' => '🌾',
        'Produits laitiers' => '🥛', 'Huiles & Graisses' => '🫒', 'Fruits secs' => '🌰'
    ];

    $parCategorie = [];
    foreach ($courseDetail['articles'] as $art) {
        $cat = $art['categorie'];
        if (!isset($parCategorie[$cat])) $parCategorie[$cat] = [];
        $parCategorie[$cat][] = $art;
    }
    ?>

    <div class="card">
        <div class="card-header">
            <h3><?php echo htmlspecialchars($courseDetail['nom']); ?></h3>
            <div class="detail-head-actions">
                <?php
                    $badgeClass = 'badge-inactif';
                    if ($courseDetail['statut'] === 'En cours') $badgeClass = 'badge-actif';
                    elseif ($courseDetail['statut'] === 'Complétée') $badgeClass = 'badge-bio';
                ?>
                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($courseDetail['statut']); ?></span>
                <a href="/Esprit-PW-2A19-2026-SportFuel/index.php?page=courses" class="btn btn-outline btn-sm">← Retour</a>
            </div>
        </div>
        <div class="detail-meta">
            <span><?php echo htmlspecialchars($courseDetail['date']); ?></span> &middot;
            <span><?php echo htmlspecialchars($currentUserName); ?></span> &middot;
            <strong><?php echo round($totalKcal); ?> kcal estimées</strong>
            <small>(unité « pièce » exclue)</small>
        </div>

        <?php foreach ($parCategorie as $cat => $items):
            $emoji = $emojis[$cat] ?? '🍽️';
        ?>
            <h4 class="category-title"><?php echo $emoji . ' ' . htmlspecialchars($cat); ?></h4>
            <div class="table-container section-table">
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Aliment</th>
                            <th>Quantité</th>
                            <th>Kcal</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $art):
                            $kcal = Course::kcalArticle($art);
                        ?>
                        <tr class="<?php echo $art['achete'] ? 'checked' : ''; ?>">
                            <td>
                                <a href="/Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&action=toggle_achete&id_course=<?php echo $courseDetail['id_course']; ?>&id_aliment=<?php echo $art['id_aliment']; ?>">
                                    <input type="checkbox" <?php echo $art['achete'] ? 'checked' : ''; ?> onclick="return false;">
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($art['nom']); ?></td>
                            <td><?php echo $art['quantite'] . ' ' . htmlspecialchars($art['unite']); ?></td>
                            <td><?php echo $kcal === null ? '—' : round($kcal); ?></td>
                            <td>
                                <?php if ($art['achete']): ?>
                                    <span class="badge badge-bio">Acheté</span>
                                <?php else: ?>
                                    <span class="badge badge-inactif">À acheter</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&action=toggle_achete&id_course=<?php echo $courseDetail['id_course']; ?>&id_aliment=<?php echo $art['id_aliment']; ?>" class="btn btn-outline btn-sm">
                                    <?php echo $art['achete'] ? 'Annuler' : 'Marquer acheté'; ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

        <div class="total-summary">
            <strong>Total kcal :</strong> <?php echo round($totalKcal); ?> kcal
        </div>
    </div>

    <?php else: ?>
    <!-- ===== LISTE DE TOUTES LES COURSES ===== -->

    <!-- Stats dynamiques -->
    <div class="stat-cards">
        <div class="stat-card">
            <div class="value"><?php echo $stats['total']; ?></div>
            <div class="label">Listes de courses</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo $stats['articles_moyen']; ?></div>
            <div class="label">Articles / liste (moy.)</div>
        </div>
        <div class="stat-card">
            <div class="value orange"><?php echo $stats['pourcent_achetes']; ?>%</div>
            <div class="label">Articles achetés</div>
        </div>
        <div class="stat-card">
            <div class="value orange"><?php echo $stats['total_kcal_global']; ?></div>
            <div class="label">Kcal cumulées</div>
        </div>
    </div>

    <div class="action-row">
        <button class="btn btn-primary" type="button" onclick="document.getElementById('optimizerModal').classList.add('active')">✨ Générer une course optimisée</button>
    </div>

    <!-- Recherche & filtres -->
    <form method="GET" action="/Esprit-PW-2A19-2026-SportFuel/index.php" class="search-bar">
        <input type="hidden" name="page" value="courses">
        <input type="text" name="q" value="<?php echo htmlspecialchars($filtre_q); ?>" placeholder="Rechercher par nom...">
        <select name="statut_filtre">
            <option value="">Tous les statuts</option>
            <option value="Non démarrée" <?php echo $filtre_statut === 'Non démarrée' ? 'selected' : ''; ?>>Non démarrée</option>
            <option value="En cours"     <?php echo $filtre_statut === 'En cours'     ? 'selected' : ''; ?>>En cours</option>
            <option value="Complétée"    <?php echo $filtre_statut === 'Complétée'    ? 'selected' : ''; ?>>Complétée</option>
        </select>
        <button class="btn btn-primary" type="submit">🔍 Rechercher</button>
        <a class="btn btn-outline" href="/Esprit-PW-2A19-2026-SportFuel/index.php?page=courses">Réinitialiser</a>
    </form>

    <p class="result-count"><?php echo count($courses); ?> liste(s) trouvée(s).</p>
    <div class="food-grid">
        <?php if (empty($courses)): ?>
            <p class="empty-hint">Aucune liste ne correspond à vos critères.</p>
        <?php else: ?>
            <?php foreach ($courses as $c):
                $badgeClass = 'badge-inactif';
                if ($c['statut'] === 'En cours') $badgeClass = 'badge-actif';
                elseif ($c['statut'] === 'Complétée') $badgeClass = 'badge-bio';
            ?>
            <div class="food-card">
                <div class="food-card-img">
                    <?php if (!empty($c['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars(cloudinary_thumb($c['image_url'], 400, 400)); ?>" alt="<?php echo htmlspecialchars($c['nom']); ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        🛒
                    <?php endif; ?>
                </div>
                <div class="food-card-body">
                    <h4><?php echo htmlspecialchars($c['nom']); ?></h4>
                    <p class="category"><?php echo htmlspecialchars($c['date']); ?> &middot; <?php echo htmlspecialchars($currentUserName); ?></p>
                    <div style="display:flex;gap:6px;margin-bottom:8px;">
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($c['statut']); ?></span>
                    </div>
                    <div class="food-card-meta">
                        <span class="kcal"><?php echo $c['nb_articles']; ?> articles</span>
                        <span class="co2"><?php echo intval($c['nb_achetes']); ?>/<?php echo $c['nb_articles']; ?> achetés</span>
                    </div>
                    <a href="/Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&id=<?php echo $c['id_course']; ?>" class="btn btn-primary btn-sm" style="margin-top:8px;">Consulter</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="modal-overlay <?php echo !empty($showOptimizerModal) ? 'active' : ''; ?>" id="optimizerModal">
        <div class="modal" style="max-width:800px;">
            <h3>Optimiseur</h3>

            <form method="POST" action="/Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&action=optimiser_preview">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom de la liste</label>
                        <input type="text" name="nom" value="<?php echo htmlspecialchars($optimizerInput['nom'] ?? 'Liste optimisée'); ?>" placeholder="Ex: Semaine prise de masse">
                    </div>
                    <div class="form-group">
                        <label>Cible kcal totale</label>
                        <input type="text" name="kcal_target" value="<?php echo htmlspecialchars((string)($optimizerInput['kcal_target'] ?? 2000)); ?>" placeholder="Ex: 2200">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Budget max (TND)</label>
                        <input type="text" name="budget_max" value="<?php echo htmlspecialchars((string)($optimizerInput['budget_max'] ?? 20)); ?>" placeholder="Ex: 25">
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;gap:18px;">
                        <label style="display:flex;align-items:center;gap:6px;font-weight:500;">
                            <input type="checkbox" name="prefer_bio" value="1" <?php echo !empty($optimizerInput['prefer_bio']) ? 'checked' : ''; ?>>
                            Prioriser Bio
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-weight:500;">
                            <input type="checkbox" name="prefer_local" value="1" <?php echo !empty($optimizerInput['prefer_local']) ? 'checked' : ''; ?>>
                            Prioriser Local
                        </label>
                    </div>
                </div>
                <div class="modal-actions" style="margin-top:4px;">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('optimizerModal').classList.remove('active')">Fermer</button>
                    <button type="submit" class="btn btn-primary">Calculer l'aperçu</button>
                </div>
            </form>

            <?php if (!empty($optimizerPreview)): ?>
                <?php if (!empty($optimizerPreview['warnings'])): ?>
                    <div class="alert alert-danger" style="margin-top:14px;">
                        <?php echo htmlspecialchars(implode(' ', $optimizerPreview['warnings'])); ?>
                    </div>
                <?php endif; ?>

                <div class="card optimizer-preview-card">
                    <h4 class="optimizer-preview-title">Aperçu du panier proposé</h4>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Aliment</th>
                                    <th>Catégorie</th>
                                    <th>Quantité</th>
                                    <th>Kcal</th>
                                    <th>Coût (TND)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($optimizerPreview['items'] ?? []) as $it): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($it['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($it['categorie']); ?></td>
                                        <td><?php echo htmlspecialchars($it['quantite'] . ' ' . $it['unite']); ?></td>
                                        <td><?php echo htmlspecialchars((string)$it['kcal']); ?></td>
                                        <td><?php echo number_format((float)$it['cout'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="optimizer-summary">
                        <span><strong>Kcal:</strong> <?php echo htmlspecialchars((string)$optimizerPreview['totals']['kcal_total']); ?> / <?php echo htmlspecialchars((string)$optimizerPreview['totals']['kcal_target']); ?></span>
                        <span><strong>Couverture:</strong> <?php echo htmlspecialchars((string)$optimizerPreview['totals']['couverture']); ?>%</span>
                        <span><strong>Coût:</strong> <?php echo number_format((float)$optimizerPreview['totals']['cout_total'], 2); ?> / <?php echo number_format((float)$optimizerPreview['totals']['budget_max'], 2); ?> TND</span>
                        <span><strong>Budget restant:</strong> <?php echo number_format((float)$optimizerPreview['totals']['restant'], 2); ?> TND</span>
                    </div>

                    <?php if (!empty($optimizerPreview['items'])): ?>
                        <form method="POST" action="/Esprit-PW-2A19-2026-SportFuel/index.php?page=courses&action=optimiser_create" class="optimizer-create-form">
                            <input type="hidden" name="nom" value="<?php echo htmlspecialchars($optimizerInput['nom']); ?>">
                            <input type="hidden" name="kcal_target" value="<?php echo htmlspecialchars((string)$optimizerInput['kcal_target']); ?>">
                            <input type="hidden" name="budget_max" value="<?php echo htmlspecialchars((string)$optimizerInput['budget_max']); ?>">
                            <input type="hidden" name="prefer_bio" value="<?php echo !empty($optimizerInput['prefer_bio']) ? '1' : '0'; ?>">
                            <input type="hidden" name="prefer_local" value="<?php echo !empty($optimizerInput['prefer_local']) ? '1' : '0'; ?>">
                            <button type="submit" class="btn btn-success">Confirmer la création</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<div class="footer">
    &copy; 2026 SportFuel — Nutrition intelligente pour sportifs | Projet Web 2A Esprit
</div>

</body>
</html>
