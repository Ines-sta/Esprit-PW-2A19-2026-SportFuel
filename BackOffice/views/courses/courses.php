<?php
// Vue: Listes de courses (BackOffice)
// Reçoit : $courses, $aliments, $users, $stats, $courseDetail, $courseEdit,
// $statutsAutorises, $unitesAutorisees, $filtre_*, $error, $success, getUserName().
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportFuel Admin — Listes de Courses</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
    <a href="/Esprit-PW-2A19-2526-SportFuel/BackOffice/index.php" class="sidebar-brand">
        <div class="sidebar-logo">SF</div>
        <span>Sport<em>Fuel</em></span>
    </a>
    <div class="sidebar-role">ADMIN</div>

    <ul class="sidebar-menu">
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/BackOffice/index.php"><span class="icon">📊</span> Dashboard</a></li>
    </ul>
    <div class="sidebar-section">Modules</div>
    <ul class="sidebar-menu">
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/BackOffice/index.php"><span class="icon">👥</span> Utilisateurs</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=back&action=listPlans"><span class="icon">🍽️</span> Plans alimentaires</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/BackOffice/views/entrainement/gestion_entrainement.html"><span class="icon">🏋️</span> Entraînements</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/BackOffice/controllers/aliment_controller.php"><span class="icon">🥗</span> Aliments</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/BackOffice/controllers/course_controller.php" class="active"><span class="icon">🛒</span> Listes de courses</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/BackOffice/views/coach/index.php"><span class="icon">🤝</span> Espace coach</a></li>
    </ul>
</aside>

<!-- ===== MAIN ===== -->
<div class="main-area">

    <div class="topbar">
        <h1>Listes de Courses</h1>
        <span class="date"><?php echo date('l j F Y'); ?></span>
    </div>

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
            <div class="label">Listes de courses</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo $stats['articles_moyen']; ?></div>
            <div class="trend">articles / liste</div>
            <div class="label">Moyenne articles</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo $stats['pourcent_achetes']; ?>%</div>
            <div class="label">Articles achetés</div>
        </div>
        <div class="stat-card">
            <div class="value orange"><?php echo $stats['total_kcal_global']; ?></div>
            <div class="trend">kcal cumulées</div>
            <div class="label">Kcal totales</div>
        </div>
    </div>

    <!-- Stats par statut -->
    <?php if (!empty($stats['par_statut'])): ?>
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header"><h3>Répartition par statut</h3></div>
        <div style="padding:16px;display:flex;gap:12px;flex-wrap:wrap;">
            <?php foreach ($stats['par_statut'] as $st):
                $cls = 'badge-inactif';
                if ($st['statut'] === 'En cours') $cls = 'badge-actif';
                elseif ($st['statut'] === 'Complétée') $cls = 'badge-bio';
            ?>
                <span class="badge <?php echo $cls; ?>"><?php echo htmlspecialchars($st['statut']); ?> : <?php echo $st['nb']; ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($courseDetail): ?>
    <!-- ===== VUE DÉTAIL D'UNE COURSE ===== -->
    <?php $totalKcal = Course::kcalTotal($courseDetail['articles']); ?>
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3><?php echo htmlspecialchars($courseDetail['nom']); ?> <small style="color:#6c757d;">#<?php echo $courseDetail['id_course']; ?></small></h3>
            <div style="display:flex;gap:8px;">
                <a href="course_controller.php?action=edit&id=<?php echo $courseDetail['id_course']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                <a href="course_controller.php" class="btn btn-outline btn-sm">← Retour</a>
            </div>
        </div>
        <div style="padding:16px;">
            <p><strong>Utilisateur :</strong> <?php echo htmlspecialchars(getUserName($users, $courseDetail['id_utilisateur'])); ?></p>
            <p><strong>Date :</strong> <?php echo htmlspecialchars($courseDetail['date']); ?></p>
            <p><strong>Statut :</strong>
                <?php
                    $badgeClass = 'badge-inactif';
                    if ($courseDetail['statut'] === 'En cours') $badgeClass = 'badge-actif';
                    elseif ($courseDetail['statut'] === 'Complétée') $badgeClass = 'badge-bio';
                ?>
                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($courseDetail['statut']); ?></span>
            </p>
            <p><strong>Total kcal estimées :</strong> <?php echo round($totalKcal); ?> kcal <small style="color:#6c757d;">(unité « pièce » exclue)</small></p>
        </div>
    </div>

    <!-- Articles -->
    <div class="card">
        <div class="card-header">
            <h3>Articles (<?php echo count($courseDetail['articles']); ?>)</h3>
            <button class="btn btn-primary" onclick="document.getElementById('modalAjoutArticle').classList.add('active')">+ Ajouter un article</button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Aliment</th>
                        <th>Catégorie</th>
                        <th>Quantité</th>
                        <th>Kcal/100g</th>
                        <th>Kcal article</th>
                        <th>Acheté</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courseDetail['articles'] as $art):
                        $kcal = Course::kcalArticle($art);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($art['nom']); ?></td>
                        <td><?php echo htmlspecialchars($art['categorie']); ?></td>
                        <td><?php echo $art['quantite'] . ' ' . htmlspecialchars($art['unite']); ?></td>
                        <td><?php echo $art['kcal_portion']; ?></td>
                        <td><?php echo $kcal === null ? '—' : round($kcal); ?></td>
                        <td>
                            <?php if ($art['achete']): ?>
                                <span class="badge badge-bio">✓ Acheté</span>
                            <?php else: ?>
                                <span class="badge badge-inactif">Non</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <form method="POST" action="course_controller.php?action=supprimer_article" style="display:inline;" onsubmit="return confirm('Retirer cet article ?');">
                                <input type="hidden" name="id_course" value="<?php echo $courseDetail['id_course']; ?>">
                                <input type="hidden" name="id_aliment" value="<?php echo $art['id_aliment']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Retirer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;"><strong>Total kcal :</strong></td>
                        <td colspan="3"><strong><?php echo round($totalKcal); ?> kcal</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <?php else: ?>
    <!-- ===== LISTE DES COURSES ===== -->

    <!-- Recherche & filtres -->
    <form method="GET" action="course_controller.php" class="search-bar" style="margin-bottom:20px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="q" value="<?php echo htmlspecialchars($filtre_q); ?>" placeholder="Rechercher par nom..." style="flex:1;min-width:180px;padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
        <select name="statut_filtre" style="padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
            <option value="">Tous les statuts</option>
            <?php foreach ($statutsAutorises as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo $filtre_statut === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
            <?php endforeach; ?>
        </select>
        <select name="user_filtre" style="padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
            <option value="">Tous les utilisateurs</option>
            <?php foreach ($users as $u): ?>
                <option value="<?php echo $u['id']; ?>" <?php echo (string)$filtre_user === (string)$u['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['nom']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="date_min" value="<?php echo htmlspecialchars($filtre_date_min); ?>" placeholder="Du (AAAA-MM-JJ)" style="padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
        <input type="text" name="date_max" value="<?php echo htmlspecialchars($filtre_date_max); ?>" placeholder="Au (AAAA-MM-JJ)" style="padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
        <button class="btn btn-primary" type="submit">🔍 Rechercher</button>
        <a class="btn btn-outline" href="course_controller.php">Réinitialiser</a>
    </form>

    <div class="card">
        <div class="card-header">
            <h3>Listes de courses (<?php echo count($courses); ?>)</h3>
            <button class="btn btn-primary" onclick="document.getElementById('modalAjout').classList.add('active')">+ Nouvelle liste</button>
        </div>

        <div class="table-container">
            <table id="tableCourses">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Utilisateur</th>
                        <th>Date</th>
                        <th>Articles</th>
                        <th>Progression</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr><td colspan="8" style="text-align:center;color:#6c757d;">Aucune liste trouvée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($courses as $c): ?>
                        <tr>
                            <td>
                                <?php if (!empty($c['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars(cloudinary_thumb($c['image_url'], 80, 80)); ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                <?php else: ?>
                                    <span style="color:#bbb;font-size:24px;">🛒</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($c['nom']); ?> <small style="color:#6c757d;">#<?php echo $c['id_course']; ?></small></td>
                            <td><?php echo htmlspecialchars(getUserName($users, $c['id_utilisateur'])); ?></td>
                            <td><?php echo htmlspecialchars($c['date']); ?></td>
                            <td><?php echo $c['nb_articles']; ?> articles</td>
                            <td><?php echo intval($c['nb_achetes']); ?>/<?php echo $c['nb_articles']; ?></td>
                            <td>
                                <?php
                                    $badgeClass = 'badge-inactif';
                                    if ($c['statut'] === 'En cours') $badgeClass = 'badge-actif';
                                    elseif ($c['statut'] === 'Complétée') $badgeClass = 'badge-bio';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($c['statut']); ?></span>
                            </td>
                            <td class="actions">
                                <a href="course_controller.php?action=voir&id=<?php echo $c['id_course']; ?>" class="btn btn-success btn-sm">Voir</a>
                                <a href="course_controller.php?action=edit&id=<?php echo $c['id_course']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                                <form method="POST" action="course_controller.php?action=supprimer" style="display:inline;" onsubmit="return confirm('Supprimer cette liste ?');">
                                    <input type="hidden" name="id" value="<?php echo $c['id_course']; ?>">
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
    <?php endif; ?>

</div>

<!-- ===== Modal: Nouvelle liste ===== -->
<div class="modal-overlay <?php echo (!empty($error) && $action === 'ajouter') ? 'active' : ''; ?>" id="modalAjout">
    <div class="modal">
        <h3>Nouvelle liste de courses</h3>
        <form method="POST" action="course_controller.php?action=ajouter" enctype="multipart/form-data" onsubmit="return validerFormCourse(this)">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom de la liste</label>
                    <input type="text" name="nom" placeholder="Ex: Courses semaine marathon">
                </div>
                <div class="form-group">
                    <label>Utilisateur</label>
                    <select name="id_utilisateur" style="width:100%;padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                        <option value="">-- Choisir un utilisateur --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date</label>
                    <input type="text" name="date" placeholder="AAAA-MM-JJ">
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut" style="width:100%;padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                        <option value="">-- Choisir un statut --</option>
                        <?php foreach ($statutsAutorises as $st): ?>
                            <option value="<?php echo $st; ?>"><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top:12px;">
                <label>Image (JPEG/PNG/WebP/GIF, max 5 Mo)</label>
                <input type="file" name="image" accept="image/*">
            </div>
            <div id="erreurAjout" style="color:#e63946;margin-top:8px;display:none;"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalAjout').classList.remove('active')">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer la liste</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Modal: Modifier ===== -->
<?php if ($courseEdit): ?>
<div class="modal-overlay active" id="modalModif">
    <div class="modal">
        <h3>Modifier la liste #<?php echo $courseEdit['id_course']; ?></h3>
        <form method="POST" action="course_controller.php?action=modifier" enctype="multipart/form-data" onsubmit="return validerFormCourse(this)">
            <input type="hidden" name="id" value="<?php echo $courseEdit['id_course']; ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom de la liste</label>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($courseEdit['nom']); ?>">
                </div>
                <div class="form-group">
                    <label>Utilisateur</label>
                    <select name="id_utilisateur" style="width:100%;padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                        <option value="">-- Choisir un utilisateur --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo (int)$courseEdit['id_utilisateur'] === (int)$u['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date</label>
                    <input type="text" name="date" value="<?php echo htmlspecialchars($courseEdit['date']); ?>">
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut" style="width:100%;padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                        <?php foreach ($statutsAutorises as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo $courseEdit['statut'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top:12px;">
                <label>Image</label>
                <?php if (!empty($courseEdit['image_url'])): ?>
                    <div style="margin-bottom:8px;"><img src="<?php echo htmlspecialchars(cloudinary_thumb($courseEdit['image_url'], 120, 120)); ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:6px;"></div>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*">
                <small style="color:#6c757d;display:block;margin-top:4px;">Laisser vide pour conserver l'image actuelle.</small>
            </div>
            <div id="erreurModif" style="color:#e63946;margin-top:8px;display:none;"></div>
            <div class="modal-actions">
                <a href="course_controller.php" class="btn btn-outline">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ===== Modal: Ajouter article ===== -->
<?php if ($courseDetail): ?>
<div class="modal-overlay" id="modalAjoutArticle">
    <div class="modal">
        <h3>Ajouter un article à « <?php echo htmlspecialchars($courseDetail['nom']); ?> »</h3>
        <form method="POST" action="course_controller.php?action=ajouter_article" onsubmit="return validerFormArticle(this)">
            <input type="hidden" name="id_course" value="<?php echo $courseDetail['id_course']; ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Aliment</label>
                    <select name="id_aliment" style="width:100%;padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                        <option value="">-- Choisir un aliment --</option>
                        <?php foreach ($aliments as $a): ?>
                            <option value="<?php echo $a['id_aliment']; ?>"><?php echo htmlspecialchars($a['nom']); ?> (<?php echo htmlspecialchars($a['categorie']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Quantité</label>
                    <input type="text" name="quantite" placeholder="Ex: 0.5, 1, 12">
                </div>
                <div class="form-group">
                    <label>Unité</label>
                    <select name="unite" style="width:100%;padding:10px 16px;border:1px solid #ddd;border-radius:8px;font-size:14px;">
                        <?php foreach ($unitesAutorisees as $u): ?>
                            <option value="<?php echo $u; ?>" <?php echo $u === 'g' ? 'selected' : ''; ?>><?php echo $u; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div id="erreurArticle" style="color:#e63946;margin-top:8px;display:none;font-size:13px;">
                <small style="color:#6c757d;display:block;">Les unités <em>g, kg, ml, L</em> permettent le calcul des kcal. <em>piece</em> n'est pas convertible et apparaît avec « — ».</small>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalAjoutArticle').classList.remove('active')">Annuler</button>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="../assets/js/validation.js"></script>

</body>
</html>
