<?php
session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../controllers/FrontOfficeController.php';

$controller = new FrontOfficeController();
$controller->handlePost();
$data = $controller->getData();

$current_user = $data['current_user'];
$publications = $data['publications'];
$db_error = $data['db_error'];
$focus = $data['focus'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);
$is_messages_page = ($current_page === 'dashboard.php');
$is_training_page = ($current_page === 'demandes-entrainement.php');
$is_nutrition_page = ($current_page === 'demandes-nutrition.php');
$is_focus_page = in_array($focus, ['entrainement', 'nutrition'], true);
$page_title = 'Mes Demandes & Suivis';
$history_title = 'Mon Historique';

if ($focus === 'entrainement') {
    $page_title = 'Mes Demandes Entraînement';
    $history_title = 'Historique Entraînement';
} elseif ($focus === 'nutrition') {
    $page_title = 'Mes Demandes Nutrition';
    $history_title = 'Historique Nutrition';
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportFuel — Mes Messages</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/coach.css">
    <style>
        .pub-card { background: white; margin-bottom: 22px; padding: 22px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .pub-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
        .pub-date { color: #888; font-size: 0.95em; font-weight: 500; letter-spacing: 0.2px; }
        .pub-meta { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 8px; }
        .pub-meta-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .pub-label { font-weight: 700; color: #22543d; letter-spacing: 0.3px; word-spacing: 2px; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 999px; font-size: 0.85em; font-weight: 700; letter-spacing: 0.2px; }
        .status-en-attente { background: #fef3c7; color: #92400e; }
        .status-en-cours { background: #dbeafe; color: #1e3a8a; }
        .status-repondu { background: #dcfce7; color: #166534; }
        .pub-toggle { background: transparent; border: 1px solid #22543d; color: #22543d; border-radius: 8px; padding: 6px 12px; cursor: pointer; }
        .pub-body { font-size: 1.1em; line-height: 1.8; color: #333; margin-bottom: 15px; word-spacing: 2px; }
        .text-preview-content { white-space: pre-wrap; line-height: 1.6; color: #333; min-height: 80px; }
        .comment-section { margin-top: 15px; padding-top: 15px; border-top: 2px dashed #eee; }
        .comment-item { background: #f8fafc; padding: 16px; border-radius: 8px; margin-bottom: 12px; border-left: 4px solid var(--vert-foret); line-height: 1.7; word-spacing: 2px; }
        .comment-header { display: flex; justify-content: space-between; margin-bottom: 5px; font-weight: bold; color: var(--vert-foret); font-size: 0.9em; }
        .form-message { width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 1.02em; margin-bottom: 15px; resize: vertical; line-height: 1.7; word-spacing: 2px; }
        .message-actions { display: flex; gap: 10px; margin-top: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; letter-spacing: 0.3px; }
        .textarea-small { min-height: 120px; }
        .request-main-card { max-width: 980px; margin: 0 auto 24px; background: linear-gradient(180deg, #dff4e7 0%, #c7e9d5 100%); border: 1px solid #a6d2b8; border-radius: 18px; box-shadow: 0 14px 28px rgba(34, 84, 61, 0.15); padding: 24px; }
        .request-main-title { margin: 0 0 6px 0; font-size: 1.25rem; color: #194634; font-weight: 800; }
        .request-main-subtitle { margin: 0 0 18px 0; color: #2d5b47; font-size: 0.95rem; }
        .single-request-box { border: 1px solid #9bc9ad; border-radius: 14px; background: #ffffff; padding: 14px; box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7); }
        .request-switch { display: flex; gap: 8px; margin-bottom: 14px; background: #eff9f2; padding: 6px; border-radius: 10px; }
        .request-switch-btn { flex: 1; border: 1px solid transparent; background: transparent; color: #1f513a; font-weight: 700; border-radius: 8px; padding: 10px 12px; cursor: pointer; transition: all .25s ease; text-align: center; }
        .request-switch-btn.active { background: #22543d; color: #fff; border-color: #22543d; }
        .request-panel { display: none; opacity: 0; transform: translateY(6px); transition: opacity .25s ease, transform .25s ease; }
        .request-panel.active { display: block; opacity: 1; transform: translateY(0); }
        .request-extra-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 10px; margin-top: 10px; }
        .request-extra-field { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; }
        .upload-area { background: #f7fbf8; border: 1px dashed #9bc9ad; border-radius: 10px; padding: 12px; }
        .selected-files-list { margin: 8px 0 0 0; padding-left: 18px; color: #2f4a3f; font-size: 0.9em; }
        .selected-files-list:empty { display: none; }
        .submit-row { display: flex; justify-content: flex-end; margin-top: 10px; }
        .insights-box { margin-top: 12px; border: 1px solid #dbe7df; background: #f8fcf9; border-radius: 10px; padding: 12px; }
        .insights-title { font-weight: 800; color: #22543d; margin-bottom: 8px; }
        .insights-line { font-size: 0.92em; color: #2f4a3f; margin-bottom: 6px; }
        .insights-suggestions { margin: 0; padding-left: 18px; font-size: 0.9em; color: #334155; }
        .critical-alert { margin-top: 8px; padding: 8px 10px; border-radius: 8px; background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; font-size: 0.9em; display: none; }
        .critical-alert.active { display: block; }
        .pub-section-title { margin-top: 14px; margin-bottom: 8px; font-weight: 800; color: #22543d; letter-spacing: 0.35px; }
        .attachment-list { margin-top: 10px; }
        .attachment-item { margin-bottom: 8px; font-size: 0.95em; }
        .attachment-item a { color: #2f855a; text-decoration: none; }
        .attachment-item a:hover { text-decoration: underline; }
        .file-note { font-size: 0.92em; color: #555; margin-top: -10px; margin-bottom: 15px; line-height: 1.6; word-spacing: 1.5px; }
        .type-select { width: 100%; padding: 14px; border-radius: 8px; border: 1px solid #ddd; background: white; font-family: inherit; font-size: 1.02em; margin-bottom: 15px; line-height: 1.6; word-spacing: 1.5px; }
        .btn { padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; }
        .btn-success { background: #22c55e; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-outline { background: transparent; border: 1px solid #22543d; color: #22543d; }
        .btn-sm { font-size: 0.9em; padding: 8px 14px; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal-overlay.active { display: flex; }
        .modal { background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; }
        .coach-header h1 { font-weight: 800; letter-spacing: 0.4px; margin-bottom: 10px; }
        .coach-header p { line-height: 1.8; word-spacing: 2px; }
        .suivi-history h2 { font-weight: 800; letter-spacing: 0.35px; margin-bottom: 14px; }
        .chatbot-toggle { position: fixed; right: 24px; bottom: 24px; width: 56px; height: 56px; border: none; border-radius: 50%; background: #22543d; color: #fff; font-size: 1.4rem; box-shadow: 0 10px 24px rgba(34, 84, 61, 0.35); cursor: pointer; z-index: 1200; transition: transform .2s ease, background .2s ease; }
        .chatbot-toggle:hover { transform: translateY(-2px); background: #1b4431; }
        .chatbot-popup { position: fixed; right: 24px; bottom: 92px; width: min(360px, calc(100vw - 24px)); background: #fff; border: 1px solid #dbe7df; border-radius: 14px; box-shadow: 0 16px 30px rgba(0, 0, 0, 0.2); overflow: hidden; z-index: 1200; opacity: 0; visibility: hidden; transform: translateY(12px) scale(0.98); transition: opacity .25s ease, transform .25s ease, visibility .25s ease; }
        .chatbot-popup.open { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
        .chatbot-head { background: #22543d; color: #fff; padding: 12px 14px; font-weight: 700; display: flex; justify-content: space-between; align-items: center; }
        .chatbot-close { border: none; background: transparent; color: #fff; font-size: 1rem; cursor: pointer; }
        .chatbot-box { border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; padding: 12px; min-height: 170px; max-height: 260px; overflow-y: auto; background: #f9fbfa; }
        .chatbot-msg { margin-bottom: 8px; line-height: 1.5; }
        .chatbot-msg strong { color: #1f513a; }
        .chatbot-input-row { display: flex; gap: 10px; padding: 10px; }
        .chatbot-input { flex: 1; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; }
        /* Calorie Calculator Button */
        .calorie-calc-toggle { position: fixed; right: 90px; bottom: 24px; width: 56px; height: 56px; border: none; border-radius: 50%; background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; font-size: 1.4rem; box-shadow: 0 10px 24px rgba(234, 88, 12, 0.4); cursor: pointer; z-index: 1200; transition: transform .2s ease, background .2s ease; }
        .calorie-calc-toggle:hover { transform: translateY(-2px); background: linear-gradient(135deg, #ea580c, #c2410c); }
        .calorie-calc-tooltip { position: fixed; right: 80px; bottom: 84px; background: rgba(30,30,30,0.85); color: #fff; font-size: 0.78rem; padding: 5px 10px; border-radius: 8px; white-space: nowrap; opacity: 0; pointer-events: none; z-index: 1201; transition: opacity .2s ease; }
        .calorie-calc-toggle:hover + .calorie-calc-tooltip { opacity: 1; }
        /* Calorie Calculator Modal */
        .calc-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); align-items: center; justify-content: center; z-index: 1300; }
        .calc-modal-overlay.active { display: flex; }
        .calc-modal { background: #fff; border-radius: 18px; width: 90%; max-width: 440px; box-shadow: 0 24px 50px rgba(0,0,0,0.25); overflow: hidden; animation: calcSlideIn .3s ease; }
        @keyframes calcSlideIn { from { transform: translateY(30px) scale(0.97); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }
        .calc-header { background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; }
        .calc-header h3 { margin: 0; font-size: 1.1rem; font-weight: 800; letter-spacing: 0.3px; }
        .calc-close { border: none; background: transparent; color: #fff; font-size: 1.2rem; cursor: pointer; }
        .calc-body { padding: 20px; }
        .calc-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
        .calc-field { display: flex; flex-direction: column; gap: 4px; }
        .calc-field label { font-size: 0.82rem; font-weight: 700; color: #374151; letter-spacing: 0.2px; }
        .calc-field input, .calc-field select { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-family: inherit; font-size: 0.95rem; background: #f9fafb; transition: border-color .2s; }
        .calc-field input:focus, .calc-field select:focus { outline: none; border-color: #f97316; }
        .calc-activity { margin-bottom: 14px; }
        .calc-activity label { display: block; font-size: 0.82rem; font-weight: 700; color: #374151; margin-bottom: 4px; }
        .calc-activity select { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-family: inherit; font-size: 0.95rem; background: #f9fafb; }
        .calc-btn { width: 100%; padding: 12px; border: none; border-radius: 10px; background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; font-weight: 700; font-size: 1rem; cursor: pointer; transition: opacity .2s; }
        .calc-btn:hover { opacity: 0.9; }
        .calc-result { margin-top: 16px; background: linear-gradient(135deg, #fff7ed, #ffedd5); border: 1px solid #fed7aa; border-radius: 12px; padding: 16px; display: none; }
        .calc-result.show { display: block; }
        .calc-result-title { font-weight: 800; color: #9a3412; font-size: 0.95rem; margin-bottom: 10px; }
        .calc-result-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .calc-result-item { background: #fff; border-radius: 8px; padding: 10px; text-align: center; border: 1px solid #fed7aa; }
        .calc-result-item .val { font-size: 1.25rem; font-weight: 800; color: #ea580c; }
        .calc-result-item .lbl { font-size: 0.72rem; color: #6b7280; margin-top: 2px; }
        .calc-result-note { font-size: 0.78rem; color: #78350f; margin-top: 10px; line-height: 1.5; }
        @media (max-width: 768px) {
            .request-main-card { padding: 16px; border-radius: 14px; }
            .request-switch { flex-direction: column; }
            .submit-row { justify-content: center; }
            .chatbot-popup { right: 12px; bottom: 84px; width: calc(100vw - 24px); }
            .chatbot-toggle { right: 12px; bottom: 18px; }
            .calorie-calc-toggle { right: 78px; bottom: 18px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="/Esprit-PW-2A19-2526-SportFuel/FrontOffice/index.php" class="navbar-brand">
        <div class="navbar-logo">SF</div>
        <span>Sport<em>Fuel</em></span>
    </a>
    <ul class="navbar-links">
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/FrontOffice/index.php">Dashboard</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=plans">Mon plan</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/FrontOffice/views/entrainement/ajout_seance.html">Entraînements</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/FrontOffice/controllers/course_controller.php">Courses</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/FrontOffice/controllers/aliment_controller.php">Aliments</a></li>
        <li><a href="dashboard.php" class="<?php echo $is_messages_page ? 'active' : ''; ?>">Mes Messages</a></li>
        <li><a href="demandes-entrainement.php" class="<?php echo $is_training_page ? 'active' : ''; ?>">Demandes entraînement</a></li>
        <li><a href="demandes-nutrition.php" class="<?php echo $is_nutrition_page ? 'active' : ''; ?>">Demandes nutrition</a></li>
    </ul>
    <div class="navbar-user"><?php echo $current_user ? substr($current_user['prenom'],0,1).substr($current_user['nom'],0,1) : 'SF'; ?></div>
</nav>

<div class="main-content">
    <div class="coach-header">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <p>Laissez vos messages, remarques ou notes. Les administrateurs / coachs vous répondront directement ici.</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <strong>Erreur :</strong> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if ($db_error): ?>
        <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <strong>Information :</strong> <?php echo $db_error; ?>
        </div>
    <?php endif; ?>

    <?php if (!$is_focus_page): ?>
    <div class="request-main-card">
        <h3 class="request-main-title">Requests & Messages</h3>
        <p class="request-main-subtitle">Envoyez vos demandes avec un format clair et structuré.</p>
        <form id="add-pub-form" action="<?php echo htmlspecialchars($current_page); ?>" method="POST" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="action" value="add_pub">

            <div class="form-group">
                <label for="request-type">Type de message</label>
                <select id="request-type" name="type" class="type-select">
                    <option value="" disabled selected>Choisissez un type de message</option>
                    <option value="Demande">Demande — poser une question au coach</option>
                    <option value="Conseil">Conseil — partager une astuce</option>
                    <option value="Problème">Problème — signaler une difficulté</option>
                    <option value="Feedback">Feedback — retour sur un plan / séance</option>
                </select>
            </div>

            <div class="form-group">
                <label>Contenu de la demande</label>
                <div class="single-request-box">
                    <div class="request-switch">
                        <button type="button" class="request-switch-btn active" data-target="panel-entrainement">Entraînement</button>
                        <button type="button" class="request-switch-btn" data-target="panel-nutrition">Nutrition</button>
                    </div>

                    <div id="panel-entrainement" class="request-panel active">
                        <textarea id="demande_entrainement" name="demande_entrainement" class="form-message textarea-small" placeholder="Décrivez votre objectif sportif, niveau, fréquence..."></textarea>
                        <div class="request-extra-grid">
                            <select id="training-level" class="request-extra-field" aria-label="Niveau entraînement">
                                <option value="">Niveau</option>
                                <option>Débutant</option>
                                <option>Intermédiaire</option>
                                <option>Avancé</option>
                            </select>
                            <select id="training-frequency" class="request-extra-field" aria-label="Fréquence hebdomadaire">
                                <option value="">Fréquence / semaine</option>
                                <option>1-2 séances</option>
                                <option>3-4 séances</option>
                                <option>5+ séances</option>
                            </select>
                        </div>
                        <div class="insights-box" id="insights-entrainement">
                            <div class="insights-title">Analyse automatique</div>
                            <div class="insights-line" id="insight-obj-entrainement">Objectif détecté : -</div>
                            <div class="insights-line" id="insight-risk-entrainement">Risque détecté : -</div>
                            <ul class="insights-suggestions" id="insight-sug-entrainement"></ul>
                            <div class="critical-alert" id="critical-alert-entrainement">⚠️ Douleur ou blessure détectée : consultez un professionnel de santé et évitez les exercices à impact.</div>
                        </div>
                    </div>
                    <div id="panel-nutrition" class="request-panel">
                        <textarea id="demande_nutrition" name="demande_nutrition" class="form-message textarea-small" placeholder="Décrivez votre objectif alimentaire, poids, contraintes..."></textarea>
                        <div class="request-extra-grid">
                            <input id="nutrition-weight" class="request-extra-field" type="text" placeholder="Objectif poids (optionnel)" aria-label="Objectif poids">
                            <input id="nutrition-constraints" class="request-extra-field" type="text" placeholder="Contraintes alimentaires (optionnel)" aria-label="Contraintes alimentaires">
                        </div>
                        <div class="insights-box" id="insights-nutrition">
                            <div class="insights-title">Analyse automatique</div>
                            <div class="insights-line" id="insight-obj-nutrition">Objectif détecté : -</div>
                            <div class="insights-line" id="insight-risk-nutrition">Risque détecté : -</div>
                            <ul class="insights-suggestions" id="insight-sug-nutrition"></ul>
                            <div class="critical-alert" id="critical-alert-nutrition">⚠️ Douleur ou blessure détectée : consultez un professionnel de santé avant de suivre un plan strict.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="attachments">Ajouter des fichiers</label>
                <div class="upload-area">
                    <input id="attachments" name="attachments[]" type="file" accept="image/*,video/*,application/pdf" multiple style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; background:#fff;">
                    <ul id="selected-files-list" class="selected-files-list"></ul>
                    <div class="file-note">Images, vidéos ou PDF. Vous pouvez ajouter plusieurs fichiers.</div>
                </div>
            </div>

            <input type="hidden" name="text" id="text-combined" value="">
            <div class="submit-row">
                <button type="submit" class="btn btn-success">Publier</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="suivi-history">
        <h2><?php echo htmlspecialchars($history_title); ?></h2>

        <?php if(empty($publications)): ?>
            <p>Vous n'avez laissé aucune publication pour le moment.</p>
        <?php else: ?>

            <?php foreach($publications as $pub): ?>
            <div class="pub-card">
                <div class="pub-header">
                    <?php
                    $displayType = 'Message';
                    if (isset($pub['type']) && trim((string)$pub['type']) !== '') {
                        $displayType = trim((string)$pub['type']);
                    } elseif (preg_match('/Type\s*:\s*(.*?)(?:\n|$)/i', (string)($pub['text'] ?? ''), $typeMatch)) {
                        $displayType = trim($typeMatch[1]);
                    }
                    ?>
                    <strong><?php echo htmlspecialchars(trim(($current_user['prenom'] ?? '') . ' ' . ($current_user['nom'] ?? '')) ?: 'Utilisateur'); ?></strong>
                    <span class="pub-date"><?php echo isset($pub['date']) && $pub['date'] ? date('d/m/Y à H:i', strtotime($pub['date'])) : '-'; ?></span>
                </div>
                <?php
                $rawText = (string)($pub['text'] ?? '');
                $plainTextToShow = trim($rawText);
                $normalizedText = str_replace("\r\n", "\n", $rawText);
                $trainingText = '';
                $nutritionText = '';
                $publicationStatusLabel = 'En attente';
                $publicationStatusClass = 'status-en-attente';

                $comments = isset($pub['commentaires']) && is_array($pub['commentaires']) ? $pub['commentaires'] : [];
                if (count($comments) > 0) {
                    $hasCoachReply = false;
                    foreach ($comments as $commentItem) {
                        if ((int)($commentItem['id_user'] ?? 0) !== (int)($current_user['user_id'] ?? 0)) {
                            $hasCoachReply = true;
                            break;
                        }
                    }
                    if ($hasCoachReply) {
                        $publicationStatusLabel = 'Répondu';
                        $publicationStatusClass = 'status-repondu';
                    } else {
                        $publicationStatusLabel = 'En cours';
                        $publicationStatusClass = 'status-en-cours';
                    }
                }

                if (preg_match('/Entra(?:î|i)nement\s*:[ \t]*(.*?)(?:\n\s*\n\s*Nutrition\s*:|$)/isu', $normalizedText, $trainingMatch)) {
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
                <div class="pub-meta">
                    <div class="pub-meta-left">
                        <div class="pub-label">Type : <?php echo htmlspecialchars($displayType); ?></div>
                        <div class="status-badge <?php echo htmlspecialchars($publicationStatusClass); ?>"><?php echo htmlspecialchars($publicationStatusLabel); ?></div>
                    </div>
                    <button type="button" class="pub-toggle" onclick='openTextPreviewModal(<?php echo json_encode($plainTextToShow, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Afficher le texte</button>
                </div>

                <?php if (!empty($pub['attachments']) && is_array($pub['attachments'])): ?>
                    <div class="attachment-list">
                        <div class="pub-section-title">Fichiers joints :</div>
                        <?php foreach ($pub['attachments'] as $filePath): ?>
                            <div class="attachment-item">
                                <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank"><?php echo basename($filePath); ?></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="message-actions">
                    <?php if ($is_focus_page): ?>
                    <button class="btn btn-primary btn-sm" onclick="openReplyModal(<?php echo $pub['id_pub']; ?>)">Répondre</button>
                    <?php endif; ?>
                    <?php if (!$is_focus_page): ?>
                    <button class="btn btn-outline btn-sm" onclick="openEditModal(<?php echo $pub['id_pub']; ?>, '<?php echo htmlspecialchars(addslashes($pub['text'])); ?>')">Modifier</button>
                    <?php endif; ?>
                    <form action="<?php echo htmlspecialchars($current_page); ?>" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_pub">
                        <input type="hidden" name="id_pub" value="<?php echo $pub['id_pub']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');">Supprimer</button>
                    </form>
                </div>

                <?php if(!empty($pub['commentaires']) && count($pub['commentaires']) > 0): ?>
                <div class="comment-section">
                    <h4 style="margin-bottom:10px; color:#555;">Réponses reçues :</h4>
                    <?php foreach($pub['commentaires'] as $cmt): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <span>Admin/Coach</span>
                            <span><?php echo isset($cmt['date']) && $cmt['date'] ? date('d/m à H:i', strtotime($cmt['date'])) : '-'; ?></span>
                        </div>
                        <div><?php echo nl2br(htmlspecialchars($cmt['text'])); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>

<?php if (!$is_focus_page): ?>
<button id="chatbot-toggle" class="chatbot-toggle" type="button" aria-label="Ouvrir le chatbot">💬</button>
<button id="calorie-calc-toggle" class="calorie-calc-toggle" type="button" aria-label="Calculateur de calories" title="Calculateur de calories">🔥</button>
<div class="calorie-calc-tooltip">Calculateur de calories</div>
<div id="chatbot-popup" class="chatbot-popup" aria-hidden="true">
    <div class="chatbot-head">
        <span>Assistant SportFuel</span>
        <button id="chatbot-close" class="chatbot-close" type="button" aria-label="Fermer">✕</button>
    </div>
    <div id="chatbot-box" class="chatbot-box">
        <div class="chatbot-msg"><strong>Bot:</strong> Posez une question sur l'entraînement ou la nutrition. Je ne réponds qu'à ces sujets.</div>
    </div>
    <div class="chatbot-input-row">
        <input id="chatbot-input" class="chatbot-input" type="text" placeholder="Ex: Quel repas avant un cardio ?">
        <button id="chatbot-send" type="button" class="btn btn-success">Envoyer</button>
    </div>
</div>

<!-- Calorie Calculator Modal -->
<div class="calc-modal-overlay" id="calc-modal-overlay">
    <div class="calc-modal">
        <div class="calc-header">
            <h3>🔥 Calculateur de Calories</h3>
            <button class="calc-close" id="calc-modal-close" type="button" aria-label="Fermer">✕</button>
        </div>
        <div class="calc-body">
            <div class="calc-row">
                <div class="calc-field">
                    <label for="calc-gender">Sexe</label>
                    <select id="calc-gender">
                        <option value="male">Homme</option>
                        <option value="female">Femme</option>
                    </select>
                </div>
                <div class="calc-field">
                    <label for="calc-age">Âge (ans)</label>
                    <input id="calc-age" type="number" min="10" max="100" placeholder="25">
                </div>
            </div>
            <div class="calc-row">
                <div class="calc-field">
                    <label for="calc-weight">Poids (kg)</label>
                    <input id="calc-weight" type="number" min="30" max="300" placeholder="70">
                </div>
                <div class="calc-field">
                    <label for="calc-height">Taille (cm)</label>
                    <input id="calc-height" type="number" min="100" max="250" placeholder="175">
                </div>
            </div>
            <div class="calc-activity">
                <label for="calc-activity">Niveau d'activité</label>
                <select id="calc-activity">
                    <option value="1.2">Sédentaire (peu ou pas d'exercice)</option>
                    <option value="1.375">Légèrement actif (1-3 jours/semaine)</option>
                    <option value="1.55" selected>Modérément actif (3-5 jours/semaine)</option>
                    <option value="1.725">Très actif (6-7 jours/semaine)</option>
                    <option value="1.9">Extrêmement actif (sport + travail physique)</option>
                </select>
            </div>
            <button class="calc-btn" id="calc-submit" type="button">Calculer mes besoins</button>
            <div class="calc-result" id="calc-result">
                <div class="calc-result-title">📊 Vos besoins caloriques journaliers</div>
                <div class="calc-result-grid">
                    <div class="calc-result-item">
                        <div class="val" id="calc-bmr">-</div>
                        <div class="lbl">BMR (métabolisme de base)</div>
                    </div>
                    <div class="calc-result-item">
                        <div class="val" id="calc-maintain">-</div>
                        <div class="lbl">Maintien du poids</div>
                    </div>
                    <div class="calc-result-item">
                        <div class="val" id="calc-loss">-</div>
                        <div class="lbl">Perte de poids (−500 kcal)</div>
                    </div>
                    <div class="calc-result-item">
                        <div class="val" id="calc-gain">-</div>
                        <div class="lbl">Prise de masse (+300 kcal)</div>
                    </div>
                </div>
                <div class="calc-result-note" id="calc-note"></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!$is_focus_page): ?>
<div class="modal-overlay" id="modal-edit-pub">
    <div class="modal">
        <h3 style="margin-bottom: 15px;">Modifier ma demande</h3>
        <form action="<?php echo htmlspecialchars($current_page); ?>" method="POST">
            <input type="hidden" name="action" value="edit_pub">
            <input type="hidden" name="id_pub" id="edit-pub-id" value="">
            <textarea name="text" id="edit-pub-text" class="form-message" rows="4"></textarea>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-edit-pub')">Annuler</button>
                <button type="submit" class="btn btn-success">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="modal-overlay" id="modal-text-preview">
    <div class="modal">
        <h3 style="margin-bottom: 15px;">Texte de la demande</h3>
        <div id="text-preview-content" class="text-preview-content"></div>
        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
            <button type="button" class="btn btn-outline" onclick="closeModal('modal-text-preview')">Fermer</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-reply">
    <div class="modal">
        <h3 style="margin-bottom: 15px;">Répondre à la demande</h3>
        <form action="<?php echo htmlspecialchars($current_page); ?>" method="POST">
            <input type="hidden" name="action" value="add_comment">
            <input type="hidden" name="id_pub" id="reply-id-pub" value="">
            <textarea name="text" id="reply-text" class="form-message" rows="4" placeholder="Écrivez votre réponse..."></textarea>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-reply')">Annuler</button>
                <button type="submit" class="btn btn-success">Envoyer</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openTextPreviewModal(text) {
        document.getElementById('text-preview-content').textContent = text || '';
        document.getElementById('modal-text-preview').classList.add('active');
    }

    function openEditModal(id_pub, text) {
        document.getElementById('edit-pub-id').value = id_pub;
        document.getElementById('edit-pub-text').value = text;
        document.getElementById('modal-edit-pub').classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }
    function openReplyModal(id_pub) {
        document.getElementById('reply-id-pub').value = id_pub;
        document.getElementById('modal-reply').classList.add('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const addForm = document.getElementById('add-pub-form');
        if (addForm) {
            const entrainementField = addForm.querySelector('textarea[name="demande_entrainement"]');
            const nutritionField = addForm.querySelector('textarea[name="demande_nutrition"]');
            const trainingLevelField = document.getElementById('training-level');
            const trainingFrequencyField = document.getElementById('training-frequency');
            const nutritionWeightField = document.getElementById('nutrition-weight');
            const nutritionConstraintsField = document.getElementById('nutrition-constraints');
            const attachmentsInput = addForm.querySelector('input[name="attachments[]"]');
            const filesList = document.getElementById('selected-files-list');
            const switchButtons = addForm.querySelectorAll('.request-switch-btn');
            const requestPanels = addForm.querySelectorAll('.request-panel');
            const trainingKeywords = ['entrainement', 'entraînement', 'seance', 'séance', 'cardio', 'musculation', 'series', 'séries', 'squat', 'pompe', 'course', 'running', 'deadlift', 'echauffement', 'échauffement'];
            const nutritionKeywords = ['nutrition', 'calorie', 'calories', 'proteine', 'protéine', 'proteines', 'protéines', 'glucide', 'glucides', 'lipide', 'lipides', 'repas', 'aliment', 'aliments', 'hydration', 'vitamine'];

            function setActivePanel(targetId) {
                requestPanels.forEach(function(panel) {
                    panel.classList.toggle('active', panel.id === targetId);
                });
                switchButtons.forEach(function(button) {
                    button.classList.toggle('active', button.getAttribute('data-target') === targetId);
                });
            }

            switchButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    setActivePanel(button.getAttribute('data-target'));
                });
            });
            setActivePanel('panel-entrainement');

            function textContainsAnyKeyword(text, keywords) {
                const normalizedText = (text || '').toLowerCase();
                return keywords.some(function(keyword) {
                    return normalizedText.includes(keyword.toLowerCase());
                });
            }

            function detectObjective(text, mode) {
                const normalized = (text || '').toLowerCase();
                if (mode === 'nutrition') {
                    if (/(perte de poids|maigrir|séche|seche|sécher|secher)/.test(normalized)) return 'Perte de poids / sèche';
                    if (/(prise de masse|masse musculaire)/.test(normalized)) return 'Prise de masse (nutrition)';
                    if (/(r[eé]gime|calorie|alimentation|nutrition)/.test(normalized)) return 'Rééquilibrage alimentaire';
                    return '-';
                }

                if (/(perte de poids|maigrir|séche|seche|sécher|secher)/.test(normalized)) return 'Perte de poids / sèche';
                if (/(prise de masse|masse musculaire)/.test(normalized)) return 'Prise de masse';
                if (/(remise en forme|reprendre le sport|forme|programme|cardio|musculation)/.test(normalized)) return 'Objectif entraînement';
                return '-';
            }

            function detectRisk(text, mode) {
                const normalized = (text || '').toLowerCase();
                if (/(blessure|douleur forte|je ne peux plus)/.test(normalized)) return 'Risque critique';
                if (/(mal au genou|probl[eè]me genou|mal au dos|probl[eè]me dos|douleur)/.test(normalized)) return 'Douleur articulaire/musculaire';
                if (mode === 'entrainement' && /(fatigue extr[eê]me|epuisement|épuisement)/.test(normalized)) return 'Fatigue élevée';
                return '-';
            }

            function buildSuggestions(text, mode) {
                const normalized = (text || '').toLowerCase();
                const suggestions = [];
                if (mode === 'nutrition') {
                    if (/(perte de poids|maigrir|séche|seche|sécher|secher)/.test(normalized)) {
                        suggestions.push('Plan nutrition sèche avec déficit calorique modéré');
                        suggestions.push('Répartition protéines/glucides/lipides adaptée');
                    }
                    if (/(prise de masse|masse musculaire)/.test(normalized)) {
                        suggestions.push('Surplus calorique contrôlé + collations protéinées');
                    }
                    if (/(calorie|r[eé]gime|alimentation|nutrition)/.test(normalized)) {
                        suggestions.push('Conseils repas équilibrés selon objectif');
                    }
                    if (/(douleur|blessure|genou|dos)/.test(normalized)) {
                        suggestions.push('Prioriser hydratation et récupération nutritionnelle');
                        suggestions.push('Consulter un professionnel si douleur persistante');
                    }
                } else {
                    if (/(perte de poids|maigrir|séche|seche|sécher|secher)/.test(normalized)) {
                        suggestions.push('Cardio progressif 3-4 séances / semaine');
                        suggestions.push('Programme full-body avec dépense énergétique élevée');
                    }
                    if (/(prise de masse|masse musculaire)/.test(normalized)) {
                        suggestions.push('Programme musculation progressif full body');
                        suggestions.push('Surcharge progressive + récupération structurée');
                    }
                    if (/(douleur|blessure|genou|dos)/.test(normalized)) {
                        suggestions.push('Proposer exercices low-impact sans charge compressive');
                        suggestions.push('Afficher recommandation de consultation professionnelle');
                    }
                    if (/(programme|cardio|musculation|seance|séance|entrainement|entraînement)/.test(normalized)) {
                        suggestions.push('Plan hebdomadaire structuré avec progression');
                    }
                }
                if (suggestions.length === 0) {
                    suggestions.push('Conseils personnalisés en attente de plus de détails');
                }
                return suggestions;
            }

            function refreshInsights(mode, text) {
                const objectiveEl = document.getElementById(mode === 'entrainement' ? 'insight-obj-entrainement' : 'insight-obj-nutrition');
                const riskEl = document.getElementById(mode === 'entrainement' ? 'insight-risk-entrainement' : 'insight-risk-nutrition');
                const sugEl = document.getElementById(mode === 'entrainement' ? 'insight-sug-entrainement' : 'insight-sug-nutrition');
                const criticalEl = document.getElementById(mode === 'entrainement' ? 'critical-alert-entrainement' : 'critical-alert-nutrition');
                const objective = detectObjective(text, mode);
                const risk = detectRisk(text, mode);
                const suggestions = buildSuggestions(text, mode);
                objectiveEl.textContent = 'Objectif détecté : ' + objective;
                riskEl.textContent = 'Risque détecté : ' + risk;
                sugEl.innerHTML = '';
                suggestions.forEach(function(item) {
                    const li = document.createElement('li');
                    li.textContent = item;
                    sugEl.appendChild(li);
                });
                criticalEl.classList.toggle('active', /(Risque critique|Douleur articulaire\/musculaire)/.test(risk));
            }

            function updateExclusiveFields() {
                const hasEntrainement = entrainementField.value.trim().length > 0;
                const hasNutrition = nutritionField.value.trim().length > 0;

                if (hasEntrainement && !hasNutrition) {
                    nutritionField.disabled = true;
                    entrainementField.disabled = false;
                    if (nutritionWeightField) nutritionWeightField.disabled = true;
                    if (nutritionConstraintsField) nutritionConstraintsField.disabled = true;
                    if (trainingLevelField) trainingLevelField.disabled = false;
                    if (trainingFrequencyField) trainingFrequencyField.disabled = false;
                } else if (hasNutrition && !hasEntrainement) {
                    entrainementField.disabled = true;
                    nutritionField.disabled = false;
                    if (trainingLevelField) trainingLevelField.disabled = true;
                    if (trainingFrequencyField) trainingFrequencyField.disabled = true;
                    if (nutritionWeightField) nutritionWeightField.disabled = false;
                    if (nutritionConstraintsField) nutritionConstraintsField.disabled = false;
                } else {
                    entrainementField.disabled = false;
                    nutritionField.disabled = false;
                    if (trainingLevelField) trainingLevelField.disabled = false;
                    if (trainingFrequencyField) trainingFrequencyField.disabled = false;
                    if (nutritionWeightField) nutritionWeightField.disabled = false;
                    if (nutritionConstraintsField) nutritionConstraintsField.disabled = false;
                }
            }

            entrainementField.addEventListener('input', updateExclusiveFields);
            nutritionField.addEventListener('input', updateExclusiveFields);
            entrainementField.addEventListener('input', function() {
                refreshInsights('entrainement', entrainementField.value);
            });
            nutritionField.addEventListener('input', function() {
                refreshInsights('nutrition', nutritionField.value);
            });
            updateExclusiveFields();
            refreshInsights('entrainement', entrainementField.value);
            refreshInsights('nutrition', nutritionField.value);

            if (attachmentsInput && filesList) {
                attachmentsInput.addEventListener('change', function() {
                    filesList.innerHTML = '';
                    Array.from(attachmentsInput.files || []).forEach(function(file) {
                        const item = document.createElement('li');
                        item.textContent = file.name;
                        filesList.appendChild(item);
                    });
                });
            }

            addForm.addEventListener('submit', function(e) {
                const type = addForm.querySelector('select[name="type"]').value;
                const entrainement = entrainementField.value.trim();
                const nutrition = nutritionField.value.trim();
                const files = addForm.querySelector('input[name="attachments[]"]').files;

                if (!type || !type.trim()) {
                    alert("Le champ type du message doit etre non vide.");
                    e.preventDefault();
                    return;
                }

                if (!entrainement && !nutrition && files.length === 0) {
                    alert("Veuillez ajouter un texte ou un fichier pour votre demande.");
                    e.preventDefault();
                    return;
                }

                if (entrainement.length > 1000 || nutrition.length > 1000) {
                    alert("Chaque champ texte ne peut pas dépasser 1000 caractères.");
                    e.preventDefault();
                    return;
                }

                if (entrainement && textContainsAnyKeyword(entrainement, nutritionKeywords)) {
                    alert("Le champ entraînement contient des mots liés à la nutrition. Veuillez corriger.");
                    e.preventDefault();
                    return;
                }

                if (nutrition && textContainsAnyKeyword(nutrition, trainingKeywords)) {
                    alert("Le champ nutrition contient des mots liés à l'entraînement. Veuillez corriger.");
                    e.preventDefault();
                    return;
                }

                document.getElementById('text-combined').value =
                    "Type : " + type + "\n\nEntraînement : " + entrainement + "\n\nNutrition : " + nutrition;
            });
        }

        const editForm = document.querySelector('#modal-edit-pub form');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                const text = document.getElementById('edit-pub-text').value;
                if (!text.trim()) {
                    alert("Le message ne peut pas être vide.");
                    e.preventDefault();
                } else if (text.length > 1000) {
                    alert("Le message ne peut pas dépasser 1000 caractères.");
                    e.preventDefault();
                }
            });
        }

        const replyForm = document.querySelector('#modal-reply form');
        if (replyForm) {
            replyForm.addEventListener('submit', function(e) {
                const text = document.getElementById('reply-text').value;
                if (!text.trim()) {
                    alert("Le message ne peut pas être vide.");
                    e.preventDefault();
                } else if (text.length > 200) {
                    alert("Le message ne peut pas dépasser 200 caractères.");
                    e.preventDefault();
                }
            });
        }

        const chatbotInput = document.getElementById('chatbot-input');
        const chatbotSend = document.getElementById('chatbot-send');
        const chatbotBox = document.getElementById('chatbot-box');
        const chatbotToggle = document.getElementById('chatbot-toggle');
        const chatbotPopup = document.getElementById('chatbot-popup');
        const chatbotClose = document.getElementById('chatbot-close');
        if (chatbotInput && chatbotSend && chatbotBox && chatbotToggle && chatbotPopup && chatbotClose) {
            const trainingKeywords = ['entrainement', 'entraînement', 'seance', 'séance', 'cardio', 'musculation', 'series', 'séries', 'squat', 'pompe', 'course', 'running', 'deadlift', 'echauffement', 'échauffement'];
            const nutritionKeywords = ['nutrition', 'calorie', 'calories', 'proteine', 'protéine', 'proteines', 'protéines', 'glucide', 'glucides', 'lipide', 'lipides', 'repas', 'aliment', 'aliments', 'hydration', 'vitamine'];

            function appendChatMessage(sender, message) {
                const line = document.createElement('div');
                line.className = 'chatbot-msg';
                line.innerHTML = '<strong>' + sender + ':</strong> ' + message;
                chatbotBox.appendChild(line);
                chatbotBox.scrollTop = chatbotBox.scrollHeight;
            }

            function includesKeyword(text, keywords) {
                const normalized = (text || '').toLowerCase();
                return keywords.some(function(keyword) {
                    return normalized.includes(keyword.toLowerCase());
                });
            }

            function getBotAnswer(question) {
                const q = (question || '').toLowerCase();
                const isTraining = includesKeyword(q, trainingKeywords);
                const isNutrition = includesKeyword(q, nutritionKeywords);

                if (!isTraining && !isNutrition) {
                    return "Je réponds uniquement aux questions liées à l'entraînement ou à la nutrition.";
                }

                if (isTraining && !isNutrition) {
                    return "Conseil entraînement: structurez votre séance en échauffement, bloc principal puis récupération. Adaptez la charge progressivement et gardez une bonne technique.";
                }

                if (isNutrition && !isTraining) {
                    return "Conseil nutrition: priorisez un repas équilibré avec protéines, glucides complexes et hydratation. Répartissez vos repas selon l'horaire d'entraînement.";
                }

                return "Votre question touche entraînement et nutrition. Exemple: combinez une progression de charge avec un apport suffisant en protéines et en eau pour optimiser la récupération.";
            }

            function submitChatQuestion() {
                const question = chatbotInput.value.trim();
                if (!question) {
                    return;
                }
                appendChatMessage('Vous', question);
                appendChatMessage('Bot', getBotAnswer(question));
                chatbotInput.value = '';
            }

            chatbotSend.addEventListener('click', submitChatQuestion);
            chatbotInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitChatQuestion();
                }
            });
            chatbotToggle.addEventListener('click', function() {
                chatbotPopup.classList.toggle('open');
            });
            chatbotClose.addEventListener('click', function() {
                chatbotPopup.classList.remove('open');
            });
        }

        // Calorie Calculator
        const calcToggle = document.getElementById('calorie-calc-toggle');
        const calcOverlay = document.getElementById('calc-modal-overlay');
        const calcClose = document.getElementById('calc-modal-close');
        const calcSubmit = document.getElementById('calc-submit');

        if (calcToggle && calcOverlay) {
            calcToggle.addEventListener('click', function() {
                calcOverlay.classList.add('active');
            });
            calcClose.addEventListener('click', function() {
                calcOverlay.classList.remove('active');
            });
            calcOverlay.addEventListener('click', function(e) {
                if (e.target === calcOverlay) calcOverlay.classList.remove('active');
            });
            calcSubmit.addEventListener('click', function() {
                const gender = document.getElementById('calc-gender').value;
                const age = parseFloat(document.getElementById('calc-age').value);
                const weight = parseFloat(document.getElementById('calc-weight').value);
                const height = parseFloat(document.getElementById('calc-height').value);
                const activity = parseFloat(document.getElementById('calc-activity').value);

                if (!age || !weight || !height || isNaN(age) || isNaN(weight) || isNaN(height)) {
                    alert('Veuillez remplir tous les champs correctement.');
                    return;
                }

                // Mifflin-St Jeor BMR formula
                let bmr;
                if (gender === 'male') {
                    bmr = 10 * weight + 6.25 * height - 5 * age + 5;
                } else {
                    bmr = 10 * weight + 6.25 * height - 5 * age - 161;
                }

                const maintain = Math.round(bmr * activity);
                const loss = Math.round(maintain - 500);
                const gain = Math.round(maintain + 300);
                bmr = Math.round(bmr);

                document.getElementById('calc-bmr').textContent = bmr + ' kcal';
                document.getElementById('calc-maintain').textContent = maintain + ' kcal';
                document.getElementById('calc-loss').textContent = loss + ' kcal';
                document.getElementById('calc-gain').textContent = gain + ' kcal';

                const noteEl = document.getElementById('calc-note');
                noteEl.textContent = '💡 Ces valeurs sont des estimations basées sur la formule Mifflin-St Jeor. Consultez un nutritionniste pour un plan personnalisé.';

                document.getElementById('calc-result').classList.add('show');
            });
        }
    });
</script>

<div class="footer">
    &copy; 2026 SportFuel — Nutrition intelligente pour sportifs
</div>

</body>
</html>