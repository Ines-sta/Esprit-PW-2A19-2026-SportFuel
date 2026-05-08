<?php
/**
 * BackOffice — Formulaire d'ajout d'un plan alimentaire
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un plan — SportFuel Admin</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'View/partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>Ajouter un plan</h1>
                <div class="page-date">Nouveau plan alimentaire</div>
            </div>
            <a href="index.php?page=back&action=listPlans" class="btn btn-outline btn-sm">&larr; Retour</a>
        </div>

        <div class="content-area">
            <div class="form-page">
                <div class="form-card">
                    <div class="form-card-header">
                        <h3>Informations du plan</h3>
                    </div>
                    <form id="addPlanForm" method="POST" action="index.php?page=back&action=addPlan" novalidate>
                        <div class="form-card-body">

                            <div class="form-group">
                                <label for="nom">Nom du plan</label>
                                <input type="text" id="nom" name="nom" placeholder="Ex: Plan musculation semaine 1" required>
                                <div class="field-msg" id="msg-nom"></div>
                            </div>

                            <div class="form-group">
                                <label for="id_utilisateur">ID Utilisateur</label>
                                <input type="number" id="id_utilisateur" name="id_utilisateur" placeholder="ID utilisateur" required min="1">
                                <div class="field-msg" id="msg-id_utilisateur"></div>
                            </div>

                            <div class="form-group">
                                <label for="type">Type de plan</label>
                                <select id="type" name="type" required>
                                    <option value="">-- Selectionner un type --</option>
                                    <option value="prise_de_masse">Prise de masse</option>
                                    <option value="perte_de_poids">Perte de poids</option>
                                    <option value="maintien">Maintien</option>
                                    <option value="endurance">Endurance</option>
                                </select>
                                <div class="field-msg" id="msg-type"></div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="kcal_cibles">Calories cibles (kcal)</label>
                                    <input type="number" id="kcal_cibles" name="kcal_cibles" placeholder="Ex: 2500" required min="1000" max="6000">
                                    <div class="field-msg" id="msg-kcal_cibles"></div>
                                </div>
                                <div class="form-group">
                                    <label for="semaine">Semaine</label>
                                    <input type="number" id="semaine" name="semaine" placeholder="1 - 52" required min="1" max="52">
                                    <div class="field-msg" id="msg-semaine"></div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date_debut">Date de debut</label>
                                    <input type="date" id="date_debut" name="date_debut" required>
                                    <div class="field-msg" id="msg-date_debut"></div>
                                </div>
                                <div class="form-group">
                                    <label for="date_fin">Date de fin</label>
                                    <input type="date" id="date_fin" name="date_fin" required>
                                    <div class="field-msg" id="msg-date_fin"></div>
                                </div>
                            </div>

                        </div>
                        <div class="form-card-footer">
                            <button type="submit" id="submitBtn" class="btn btn-accent">Enregistrer le plan</button>
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
