<?php
// FrontOffice entry point — convenience landing page
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SportFuel</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div style="max-width:560px;margin:80px auto;text-align:center;font-family:'Segoe UI',sans-serif;">
    <h1 style="color:#2d6a4f;">Sport<em>Fuel</em></h1>
    <p style="color:#6c757d;margin-bottom:8px;">Nutrition intelligente pour sportifs</p>
    <p style="color:#2d6a4f;margin-bottom:32px;font-weight:600;">Session démo: connecté en tant que Ines Sta</p>
    <div style="display:flex;flex-direction:column;gap:12px;">
        <a class="btn btn-primary" href="controllers/aliment_controller.php">🥗 Catalogue d'aliments</a>
        <a class="btn btn-primary" href="controllers/course_controller.php">🛒 Mes listes de courses</a>
        <a class="btn btn-primary" href="views/entrainement/ajout_seance.html">🏋️ Mes entraînements</a>
        <a class="btn btn-primary" href="views/coach/dashboard.php">💬 Messages et demandes</a>
        <a class="btn btn-outline" href="../index.php?page=plans">🍽️ Mon plan alimentaire</a>
    </div>
</div>
</body>
</html>
