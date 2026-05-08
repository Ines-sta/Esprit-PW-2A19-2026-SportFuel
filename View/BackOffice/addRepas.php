<?php
/**
 * BackOffice — Formulaire d'ajout d'un repas
 */
require_once 'Controller/PlanAlimentaireController.php';
$planController = new PlanAlimentaireController();
$plans = $planController->listPlans();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un repas — SportFuel Admin</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'View/partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>Ajouter un repas</h1>
                <div class="page-date">Nouveau repas</div>
            </div>
            <a href="index.php?page=back&action=listRepas" class="btn btn-outline btn-sm">&larr; Retour</a>
        </div>

        <div class="content-area">
            <div class="form-page">
                <div class="form-card">
                    <div class="form-card-header">
                        <h3>Informations du repas</h3>
                    </div>
                    <form id="addRepasForm" method="POST" action="index.php?page=back&action=addRepas" novalidate>
                        <div class="form-card-body">

                            <div class="form-group">
                                <label for="id_plan">Plan alimentaire</label>
                                <select id="id_plan" name="id_plan" required>
                                    <option value="">-- Selectionner un plan --</option>
                                    <?php foreach ($plans as $plan): ?>
                                        <option value="<?= $plan->getIdPlan() ?>">
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
                                        <?php foreach (['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'] as $j): ?>
                                            <option value="<?= $j ?>"><?= $j ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="field-msg" id="msg-jour"></div>
                                </div>
                                <div class="form-group">
                                    <label for="type_repas">Type de repas</label>
                                    <select id="type_repas" name="type_repas" required>
                                        <option value="">-- Type --</option>
                                        <option value="petit_dejeuner">Petit-dejeuner</option>
                                        <option value="dejeuner">Dejeuner</option>
                                        <option value="diner">Diner</option>
                                        <option value="collation">Collation</option>
                                    </select>
                                    <div class="field-msg" id="msg-type_repas"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" placeholder="Decrivez le repas..." required minlength="10"></textarea>
                                <div class="field-msg" id="msg-description"></div>
                            </div>

                            <div class="form-group">
                                <label for="kcal">Calories (kcal)</label>
                                <input type="number" id="kcal" name="kcal" placeholder="Ex: 450" required min="50" max="2000">
                                <div class="field-msg" id="msg-kcal"></div>
                            </div>

                        </div>
                        <div class="form-card-footer">
                            <button type="submit" id="submitRepasBtn" class="btn btn-accent">Enregistrer le repas</button>
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
