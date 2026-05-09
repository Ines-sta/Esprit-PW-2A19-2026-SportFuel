<?php
/**
 * BackOffice — Modification d'un repas
 */
require_once 'Controller/RepasController.php';
require_once 'Controller/PlanAlimentaireController.php';
$repasController = new RepasController();
$planController  = new PlanAlimentaireController();

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php?page=back&action=listRepas'); exit; }
$repas = $repasController->getRepas($id);
if (!$repas) { header('Location: index.php?page=back&action=listRepas'); exit; }
$plans = $planController->listPlans();
$jours = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le repas — SportFuel Admin</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'View/partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>Modifier le repas</h1>
                <div class="page-date">Repas #<?= htmlspecialchars($repas->getIdRepas()) ?></div>
            </div>
            <a href="index.php?page=back&action=listRepas" class="btn btn-outline btn-sm">&larr; Retour</a>
        </div>

        <div class="content-area">
            <div class="form-page">
                <div class="form-card">
                    <div class="form-card-header">
                        <h3>Modifier le repas</h3>
                        <span class="badge badge-<?= $repas->getTypeRepas() ?>"><?= str_replace('_',' ', $repas->getTypeRepas()) ?></span>
                    </div>
                    <form id="addRepasForm" method="POST" action="index.php?page=back&action=updateRepas&id=<?= $repas->getIdRepas() ?>" novalidate>
                        <div class="form-card-body">

                            <div class="form-group">
                                <label for="id_plan">Plan alimentaire</label>
                                <select id="id_plan" name="id_plan" required>
                                    <option value="">-- Selectionner un plan --</option>
                                    <?php foreach ($plans as $plan): ?>
                                        <option value="<?= $plan->getIdPlan() ?>" <?= $repas->getIdPlan() == $plan->getIdPlan() ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($plan->getNom()) ?> (S<?= $plan->getSemaine() ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="field-msg" id="msg-id_plan"></div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="jour">Jour</label>
                                    <select id="jour" name="jour" required>
                                        <option value="">-- Jour --</option>
                                        <?php foreach ($jours as $j): ?>
                                            <option value="<?= $j ?>" <?= $repas->getJour() === $j ? 'selected' : '' ?>><?= $j ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="field-msg" id="msg-jour"></div>
                                </div>
                                <div class="form-group">
                                    <label for="type_repas">Type de repas</label>
                                    <select id="type_repas" name="type_repas" required>
                                        <option value="">-- Type --</option>
                                        <?php foreach (['petit_dejeuner'=>'Petit-dejeuner','dejeuner'=>'Dejeuner','diner'=>'Diner','collation'=>'Collation'] as $v => $l): ?>
                                            <option value="<?= $v ?>" <?= $repas->getTypeRepas() === $v ? 'selected' : '' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="field-msg" id="msg-type_repas"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" required minlength="10"><?= htmlspecialchars($repas->getDescription()) ?></textarea>
                                <div class="field-msg" id="msg-description"></div>
                            </div>

                            <div class="form-group">
                                <label for="kcal">Calories (kcal)</label>
                                <input type="number" id="kcal" name="kcal" value="<?= htmlspecialchars($repas->getKcal()) ?>" required min="50" max="2000">
                                <div class="field-msg" id="msg-kcal"></div>
                            </div>

                        </div>
                        <div class="form-card-footer">
                            <button type="submit" id="submitRepasBtn" class="btn btn-accent">Enregistrer les modifications</button>
                            <a href="index.php?page=back&action=listRepas" class="btn btn-outline">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php include 'View/partials/footer.php'; ?>
    </div>
</div>
<script src="public/js/addRepas.js"></script>
</body>
</html>
