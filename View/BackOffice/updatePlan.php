<?php
/**
 * BackOffice — Modification d'un plan alimentaire
 */
require_once 'Controller/PlanAlimentaireController.php';
$planController = new PlanAlimentaireController();
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php?page=back&action=listPlans'); exit; }
$plan = $planController->getPlan($id);
if (!$plan) { header('Location: index.php?page=back&action=listPlans'); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le plan — SportFuel Admin</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'View/partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>Modifier le plan</h1>
                <div class="page-date"><?= htmlspecialchars($plan->getNom()) ?></div>
            </div>
            <a href="index.php?page=back&action=listPlans" class="btn btn-outline btn-sm">&larr; Retour</a>
        </div>

        <div class="content-area">
            <div class="form-page">
                <div class="form-card">
                    <div class="form-card-header">
                        <h3>Modifier : <?= htmlspecialchars($plan->getNom()) ?></h3>
                        <span class="badge badge-<?= $plan->getType() ?>"><?= str_replace('_',' ', $plan->getType()) ?></span>
                    </div>
                    <form id="addPlanForm" method="POST" action="index.php?page=back&action=updatePlan&id=<?= $plan->getIdPlan() ?>" novalidate>
                        <div class="form-card-body">

                            <div class="form-group">
                                <label for="nom">Nom du plan</label>
                                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($plan->getNom()) ?>" required>
                                <div class="field-msg" id="msg-nom"></div>
                            </div>

                            <div class="form-group">
                                <label for="id_utilisateur">ID Utilisateur</label>
                                <input type="number" id="id_utilisateur" name="id_utilisateur" value="<?= htmlspecialchars($plan->getIdUtilisateur()) ?>" required min="1">
                                <div class="field-msg" id="msg-id_utilisateur"></div>
                            </div>

                            <div class="form-group">
                                <label for="type">Type de plan</label>
                                <select id="type" name="type" required>
                                    <option value="">-- Selectionner --</option>
                                    <?php foreach (['prise_de_masse'=>'Prise de masse','perte_de_poids'=>'Perte de poids','maintien'=>'Maintien','endurance'=>'Endurance'] as $v => $l): ?>
                                        <option value="<?= $v ?>" <?= $plan->getType() === $v ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="field-msg" id="msg-type"></div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="kcal_cibles">Calories cibles (kcal)</label>
                                    <input type="number" id="kcal_cibles" name="kcal_cibles" value="<?= htmlspecialchars($plan->getKcalCibles()) ?>" required min="1000" max="6000">
                                    <div class="field-msg" id="msg-kcal_cibles"></div>
                                </div>
                                <div class="form-group">
                                    <label for="semaine">Semaine</label>
                                    <input type="number" id="semaine" name="semaine" value="<?= htmlspecialchars($plan->getSemaine()) ?>" required min="1" max="52">
                                    <div class="field-msg" id="msg-semaine"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date_debut">Date de debut</label>
                                    <input type="date" id="date_debut" name="date_debut" value="<?= htmlspecialchars($plan->getDateDebut()) ?>" required>
                                    <div class="field-msg" id="msg-date_debut"></div>
                                </div>
                                <div class="form-group">
                                    <label for="date_fin">Date de fin</label>
                                    <input type="date" id="date_fin" name="date_fin" value="<?= htmlspecialchars($plan->getDateFin()) ?>" required>
                                    <div class="field-msg" id="msg-date_fin"></div>
                                </div>
                            </div>

                        </div>
                        <div class="form-card-footer">
                            <button type="submit" id="submitBtn" class="btn btn-accent">Enregistrer les modifications</button>
                            <a href="index.php?page=back&action=listPlans" class="btn btn-outline">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php include 'View/partials/footer.php'; ?>
    </div>
</div>
<script src="public/js/addPlan.js"></script>
</body>
</html>
