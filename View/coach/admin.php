<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/coach/CoachAdminController.php';
require_once __DIR__ . '/../partials/avatar.php';

$controller = new CoachAdminController();
$controller->handlePost();
$data = $controller->getData();

$publications = $data['publications'];
$commentaires = $data['commentaires'];
$users = $data['users'];
$db_error = $data['db_error'];
$focus = $data['focus'] ?? '';
$search = $data['search'] ?? '';
$sort = $data['sort'] ?? 'desc';
$stats = $data['stats'] ?? [];
$usingCanonicalCoachRoute = (($_GET['page'] ?? '') === 'coach');
if ($usingCanonicalCoachRoute) {
    $current_page = '/Esprit-PW-2A19-2526-SportFuel/index.php?page=coach';
    if (in_array($focus, ['entrainement', 'nutrition'], true)) {
        $current_page .= '&focus=' . rawurlencode($focus);
    }
} else {
    $current_page = basename($_SERVER['PHP_SELF']);
}
$is_all_page = ($focus === '');
$is_training_page = ($focus === 'entrainement');
$is_nutrition_page = ($focus === 'nutrition');
$is_focus_page = in_array($focus, ['entrainement', 'nutrition'], true);
$page_heading = 'Gestion Publications & Commentaires';
$publication_heading = '📝 Liste des Publications';

if ($focus === 'entrainement') {
    $page_heading = 'Demandes Entraînement';
    $publication_heading = '🏋️ Demandes Entraînement';
} elseif ($focus === 'nutrition') {
    $page_heading = 'Demandes Nutrition';
    $publication_heading = '🥗 Demandes Nutrition';
}

setlocale(LC_TIME, 'fr_FR.UTF8', 'fr.UTF8', 'fr_FR.UTF-8', 'fr_FR');
$date_jour = date('l j F Y');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportFuel Admin — Publications & Commentaires</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2526-SportFuel/public/css/style.css">
    <link rel="stylesheet" href="/Esprit-PW-2A19-2526-SportFuel/public/css/coach.css">
</head>
<body class="coach-admin-page">

<!-- SIDEBAR -->
<?php
$sidebarActive = 'coach';
if ($focus === 'entrainement') {
    $sidebarActive = 'coach-training';
} elseif ($focus === 'nutrition') {
    $sidebarActive = 'coach-nutrition';
}
include __DIR__ . '/../partials/backoffice_sidebar.php';
?>

<!-- CONTENU -->
<div class="main-area">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($page_heading); ?></h1>
        <div class="page-date"><?php echo htmlspecialchars($date_jour); ?></div>
    </div>

    <div class="content-area coach-admin-content">

    <?php if (isset($_SESSION['error'])): ?>
        <script>
            alert("Erreur : <?php echo addslashes($_SESSION['error']); ?>");
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if ($db_error): ?>
        <div class="coach-admin-notice">
            <strong>Information :</strong> <?php echo $db_error; ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <!-- Table Publications -->
        <div class="card coach-admin-panel coach-admin-publications">
            <div class="card-header">
                <h3><?php echo htmlspecialchars($publication_heading); ?></h3>
            </div>
        <?php
            $pendingUrgentCount = 0;
            foreach ($publications as $publicationItem) {
                $prio = strtolower((string)($publicationItem['priorite'] ?? 'normal'));
                $statut = (string)($publicationItem['statut'] ?? 'En attente');
                if ($prio === 'urgent' && $statut !== 'Répondu') {
                    $pendingUrgentCount++;
                }
            }
        ?>
        <?php if ($pendingUrgentCount > 0): ?>
            <div class="urgent-alert">⚠️ <?php echo (int)$pendingUrgentCount; ?> demande(s) urgente(s) non traitée(s).</div>
        <?php endif; ?>
            <div class="coach-admin-toolbar">
                <form method="GET" action="<?php echo htmlspecialchars($current_page); ?>" class="coach-admin-search-form">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Rechercher nom ou prénom..." class="coach-admin-search">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                    <button type="submit" class="btn btn-outline">Rechercher</button>
                </form>

                <div class="coach-admin-toolbar-actions">
                    <form method="GET" action="<?php echo htmlspecialchars($current_page); ?>" class="coach-admin-toolbar-inline">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="sort" value="<?php echo $sort === 'asc' ? 'desc' : 'asc'; ?>">
                        <button type="submit" class="btn btn-outline">Trier (Date <?php echo strtoupper($sort); ?>)</button>
                    </form>

                    <button type="button" class="btn btn-outline" onclick="openModal('modal-stats')">Statistique (%)</button>
                </div>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nom d’utilisateur</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Priorité</th>
                            <th>Score</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($publications) === 0): ?>
                        <tr><td colspan="7" style="text-align: center;">Aucune publication disponible.</td></tr>
                        <?php endif; ?>
                        
                        <?php foreach($publications as $p): ?>
                        <tr>
                            <td>
                                <div class="coach-inline-user">
                                    <?php echo sportfuel_avatar_markup(trim((string)(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? ''))) ?: 'Utilisateur', (string)($p['photo_profil_url'] ?? ''), 'coach-inline-avatar'); ?>
                                    <strong><?php echo htmlspecialchars(($p['prenom'] ?? '') . ' ' . ($p['nom'] ?? '')); ?></strong>
                                </div>
                            </td>
                            <td><?php echo isset($p['date']) && $p['date'] ? date('d/m/Y H:i', strtotime($p['date'])) : '-'; ?></td>
                            <td>
                                <?php
                                    $publicationType = '-';
                                    if (preg_match('/Type\s*:\s*(.*?)(?:\n|$)/i', (string)($p['text'] ?? ''), $typeMatch)) {
                                        $publicationType = trim($typeMatch[1]) !== '' ? trim($typeMatch[1]) : '-';
                                    }
                                    echo htmlspecialchars($publicationType);
                                ?>
                            </td>
                            <td>
                                <?php
                                    $priorityRaw = strtolower((string)($p['priorite'] ?? 'normal'));
                                    $priorityClass = 'priority-normal';
                                    $priorityLabel = 'Normal';
                                    $priorityIcon = '💬';
                                    if ($priorityRaw === 'urgent') {
                                        $priorityClass = 'priority-urgent';
                                        $priorityLabel = 'Urgent';
                                        $priorityIcon = '⚠️';
                                    } elseif ($priorityRaw === 'important') {
                                        $priorityClass = 'priority-important';
                                        $priorityLabel = 'Important';
                                        $priorityIcon = '📈';
                                    }
                                    $elapsedLabel = '-';
                                    if (!empty($p['date']) && strtotime((string)$p['date']) !== false) {
                                        $seconds = time() - strtotime((string)$p['date']);
                                        if ($seconds < 3600) {
                                            $elapsedLabel = floor($seconds / 60) . ' min';
                                        } elseif ($seconds < 86400) {
                                            $elapsedLabel = floor($seconds / 3600) . ' h';
                                        } else {
                                            $elapsedLabel = floor($seconds / 86400) . ' j';
                                        }
                                    }
                                ?>
                                <span class="priority-badge <?php echo $priorityClass; ?>"><?php echo $priorityIcon; ?> <?php echo htmlspecialchars($priorityLabel); ?></span>
                                <span class="elapsed-time">Il y a <?php echo htmlspecialchars($elapsedLabel); ?></span>
                            </td>
                            <td><?php echo (int)($p['effective_priority_score'] ?? $p['priority_score'] ?? 30); ?></td>
                            <td><?php echo htmlspecialchars((string)($p['statut'] ?? 'En attente')); ?></td>
                            <td>
                                <div class="actions">
                                    <button class="btn btn-outline btn-sm" onclick="openTextModalById(<?php echo (int)$p['id_pub']; ?>)">Afficher le texte</button>
                                    <button class="btn btn-primary btn-sm" onclick="openReplyModal(<?php echo $p['id_pub']; ?>)">Répondre</button>
                                    <form action="<?php echo htmlspecialchars($current_page); ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_pub">
                                        <input type="hidden" name="id_pub" value="<?php echo $p['id_pub']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette publication ?');">Suppr</button>
                                    </form>
                                </div>
                                <?php
                                    $rawText = (string)($p['text'] ?? '');
                                    $plainTextToShow = trim($rawText);
                                    $normalizedText = str_replace("\r\n", "\n", $rawText);
                                    $trainingText = '';
                                    $nutritionText = '';

                                    if (preg_match('/Entra[^:\n]*\s*:[ \t]*(.*?)(?:\n\s*\n\s*Nutrition\s*:|$)/isu', $normalizedText, $trainingMatch)) {
                                        $trainingText = trim($trainingMatch[1]);
                                    }
                                    if (preg_match('/Nutrition\s*:[ \t]*(.*)$/isu', $normalizedText, $nutritionMatch)) {
                                        $nutritionText = trim($nutritionMatch[1]);
                                    }

                                    if ($focus === 'entrainement') {
                                        $plainTextToShow = $trainingText !== '' ? $trainingText : trim($rawText);
                                    } elseif ($focus === 'nutrition') {
                                        $plainTextToShow = $nutritionText !== '' ? $nutritionText : trim($rawText);
                                    } elseif ($trainingText !== '' || $nutritionText !== '') {
                                        $plainTextToShow = trim($trainingText . "\n\n" . $nutritionText);
                                    }
                                ?>
                                <div id="pub-text-<?php echo (int)$p['id_pub']; ?>" style="display:none;"><?php echo htmlspecialchars($plainTextToShow); ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Table Commentaires -->
        <div class="card coach-admin-panel coach-admin-comments">
            <div class="card-header">
                <h3>💬 Vos Réponses (Commentaires)</h3>
                <button class="btn btn-success" onclick="openModal('modal-add-comment-manual')">+ Ajouter Commentaire</button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Réf. Publication</th>
                            <th>Votre Réponse</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($commentaires) === 0): ?>
                        <tr><td colspan="4" style="text-align: center;">Aucun commentaire disponible.</td></tr>
                        <?php endif; ?>

                        <?php foreach($commentaires as $c): ?>
                        <tr>
                            <td><?php echo isset($c['date']) && $c['date'] ? date('d/m/Y H:i', strtotime($c['date'])) : '-'; ?></td>
                            <td><span class="badge badge-local">Pub #<?php echo $c['id_pub']; ?></span></td>
                            <td>
                                <div class="coach-inline-user">
                                    <?php echo sportfuel_avatar_markup($c['nom'] ?? 'Coach', (string)($c['photo_profil_url'] ?? ''), 'coach-inline-avatar'); ?>
                                    <span><?php echo nl2br(htmlspecialchars(substr($c['text'], 0, 50))) . (strlen($c['text']) > 50 ? '...' : ''); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="actions">
                                    <button class="btn btn-outline btn-sm" onclick="openCommentTextModalById(<?php echo (int)$c['id_cmmnt']; ?>)">Afficher texte</button>
                                    <?php if (!$is_focus_page): ?>
                                    <form action="<?php echo htmlspecialchars($current_page); ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_comment">
                                        <input type="hidden" name="id_cmmnt" value="<?php echo $c['id_cmmnt']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce commentaire ?');">Suppr</button>
                                    </form>
                                    <?php else: ?>
                                    <span>-</span>
                                    <?php endif; ?>
                                </div>
                                <div id="comment-text-<?php echo (int)$c['id_cmmnt']; ?>" style="display:none;"><?php echo htmlspecialchars((string)($c['text'] ?? '')); ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!-- ================= MODALS PUBLICATION ================= -->

<!-- Modal Editer Publication -->
<?php if (!$is_focus_page): ?>
<div class="modal-overlay" id="modal-edit-pub">
    <div class="modal">
        <h3>Editer la Publication</h3>
        <form action="<?php echo htmlspecialchars($current_page); ?>" method="POST">
            <input type="hidden" name="action" value="edit_pub">
            <input type="hidden" name="id_pub" id="edit-pub-id" value="">
            <div class="form-group">
                <label>Nouveau Message</label>
                <textarea name="text" id="edit-pub-text" rows="4"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-edit-pub')">Annuler</button>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ================= MODALS COMMENTAIRE ================= -->

<!-- Modal Ajouter Commentaire Manuellement -->
<div class="modal-overlay" id="modal-add-comment-manual">
    <div class="modal">
        <h3>Lier un nouveau Commentaire</h3>
        <form action="<?php echo htmlspecialchars($current_page); ?>" method="POST">
            <input type="hidden" name="action" value="add_comment_manual">
            <div class="form-group">
                <label>Publication Réf.</label>
                <select name="id_pub">
                    <option value="">-- Sélectionner une publication --</option>
                    <?php foreach($publications as $p): ?>
                        <option value="<?php echo $p['id_pub']; ?>">Pub #<?php echo $p['id_pub']; ?> - <?php echo substr($p['text'], 0, 30); ?>...</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Votre Réponse</label>
                <textarea name="text" rows="4"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-add-comment-manual')">Annuler</button>
                <button type="submit" class="btn btn-success">Créer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Répondre Publication (= Add Comment) -->
<div class="modal-overlay" id="modal-reply">
    <div class="modal">
        <h3>Répondre à la Publication</h3>
        <form action="<?php echo htmlspecialchars($current_page); ?>" method="POST">
            <input type="hidden" name="action" value="add_comment">
            <input type="hidden" name="id_pub" id="reply-id-pub" value="">
            <div class="form-group">
                <label>Votre Réponse</label>
                <textarea name="text" rows="4" placeholder="Écrivez votre réponse ici..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-reply')">Annuler</button>
                <button type="submit" class="btn btn-primary">Envoyer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Afficher Texte Demande -->
<div class="modal-overlay" id="modal-text-view">
    <div class="modal">
        <h3>Texte de la demande</h3>
        <div id="modal-text-view-content" style="white-space: pre-wrap; line-height: 1.6; margin-top: 10px;"></div>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" onclick="closeModal('modal-text-view')">Fermer</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-stats">
    <div class="modal">
        <h3>Statistiques des types (%)</h3>
        <?php if (empty($stats)): ?>
            <p style="margin-top:10px;">Aucune donnée disponible.</p>
        <?php else: ?>
            <?php
                $wheelColors = ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316', '#64748b'];
                $segments = [];
                $legendItems = [];
                $currentAngle = 0.0;
                foreach ($stats as $index => $stat) {
                    $color = $wheelColors[$index % count($wheelColors)];
                    $percentage = (float)$stat['percentage'];
                    $nextAngle = $currentAngle + ($percentage * 3.6);
                    $segments[] = $color . ' ' . $currentAngle . 'deg ' . $nextAngle . 'deg';
                    $legendItems[] = [
                        'type' => $stat['type'],
                        'color' => $color,
                        'percentage' => $stat['percentage'],
                        'count' => $stat['count'],
                    ];
                    $currentAngle = $nextAngle;
                }
                $wheelStyle = 'background: conic-gradient(' . implode(', ', $segments) . ');';
            ?>
            <div style="display:flex; gap:20px; align-items:flex-start; margin-top:12px; flex-wrap:wrap;">
                <div style="width:180px; height:180px; border-radius:50%; <?php echo $wheelStyle; ?> border:1px solid #e5e7eb;"></div>
                <div style="flex:1; min-width:220px;">
                    <?php foreach ($legendItems as $item): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #eee;">
                            <span style="display:flex; align-items:center; gap:8px;">
                                <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:<?php echo htmlspecialchars($item['color']); ?>;"></span>
                                <?php echo htmlspecialchars($item['type']); ?>
                            </span>
                            <span><?php echo htmlspecialchars((string)$item['percentage']); ?>% (<?php echo (int)$item['count']; ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" onclick="closeModal('modal-stats')">Fermer</button>
        </div>
    </div>
</div>

<!-- Modal Editer Commentaire -->
<?php if (!$is_focus_page): ?>
<div class="modal-overlay" id="modal-edit">
    <div class="modal">
        <h3>Editer le Commentaire</h3>
        <form action="<?php echo htmlspecialchars($current_page); ?>" method="POST">
            <input type="hidden" name="action" value="edit_comment">
            <input type="hidden" name="id_cmmnt" id="edit-id-cmmnt" value="">
            <div class="form-group">
                <label>Modifier votre Réponse</label>
                <textarea name="text" id="edit-text" rows="4"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-edit')">Annuler</button>
                <button type="submit" class="btn btn-success">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    // Validation functions
    function validateText(text, maxLength) {
        if (!text.trim()) {
            return "Le champ ne peut pas être vide.";
        }
        if (text.length > maxLength) {
            return "Le texte ne peut pas dépasser " + maxLength + " caractères.";
        }
        return null;
    }

    // Attach validation to forms
    document.addEventListener('DOMContentLoaded', function() {
        // Edit pub form
        const editPubForm = document.querySelector('#modal-edit-pub form');
        if (editPubForm) {
            editPubForm.addEventListener('submit', function(e) {
                const text = document.getElementById('edit-pub-text').value;
                const error = validateText(text, 500);
                if (error) {
                    alert(error);
                    e.preventDefault();
                }
            });
        }

        // Add comment manual form
        const addCommentForm = document.querySelector('#modal-add-comment-manual form');
        if (addCommentForm) {
            addCommentForm.addEventListener('submit', function(e) {
                const text = addCommentForm.querySelector('textarea[name="text"]').value;
                const error = validateText(text, 200);
                if (error) {
                    alert(error);
                    e.preventDefault();
                }
            });
        }

        // Reply form
        const replyForm = document.querySelector('#modal-reply form');
        if (replyForm) {
            replyForm.addEventListener('submit', function(e) {
                const text = replyForm.querySelector('textarea[name="text"]').value;
                const error = validateText(text, 200);
                if (error) {
                    alert(error);
                    e.preventDefault();
                }
            });
        }

        // Edit comment form
        const editCommentForm = document.querySelector('#modal-edit form');
        if (editCommentForm) {
            editCommentForm.addEventListener('submit', function(e) {
                const text = document.getElementById('edit-text').value;
                const error = validateText(text, 200);
                if (error) {
                    alert(error);
                    e.preventDefault();
                }
            });
        }
    });

    // Handlers Publication
    function openEditPubModal(id_pub, text) {
        document.getElementById('edit-pub-id').value = id_pub;
        document.getElementById('edit-pub-text').value = text;
        openModal('modal-edit-pub');
    }
    function openTextModalById(idPub) {
        const textContainer = document.getElementById('pub-text-' + idPub);
        const text = textContainer ? textContainer.textContent : '';
        document.getElementById('modal-text-view-content').textContent = text || '';
        openModal('modal-text-view');
    }
    function openCommentTextModalById(idComment) {
        const textContainer = document.getElementById('comment-text-' + idComment);
        const text = textContainer ? textContainer.textContent : '';
        document.getElementById('modal-text-view-content').textContent = text || '';
        openModal('modal-text-view');
    }

    // Handlers Commentaire
    function openReplyModal(id_pub) {
        document.getElementById('reply-id-pub').value = id_pub;
        openModal('modal-reply');
    }
    function openEditModal(id_cmmnt, text) {
        document.getElementById('edit-id-cmmnt').value = id_cmmnt;
        document.getElementById('edit-text').value = text;
        openModal('modal-edit');
    }
</script>

</body>
</html>