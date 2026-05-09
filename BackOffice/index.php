<?php
// BackOffice entry point — convenience landing page
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SportFuel — BackOffice</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div style="max-width:560px;margin:80px auto;text-align:center;font-family:'Segoe UI',sans-serif;">
    <h1 style="color:#2d6a4f;">SportFuel — BackOffice</h1>
    <p style="color:#6c757d;margin-bottom:32px;">Espace administrateur</p>
    <div style="display:flex;flex-direction:column;gap:12px;">
        <a class="btn btn-primary" href="controllers/aliment_controller.php">🥗 Gérer les aliments</a>
        <a class="btn btn-primary" href="controllers/course_controller.php">🛒 Gérer les listes de courses</a>
        <a class="btn btn-primary" href="views/entrainement/gestion_entrainement.html">🏋️ Gérer les entraînements</a>
        <a class="btn btn-primary" href="views/coach/index.php">🤝 Publications et suivi coach</a>
        <a class="btn btn-outline" href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=back&action=listPlans">🍽️ Plans alimentaires</a>
        <a class="btn btn-outline" href="/Esprit-PW-2A19-2526-SportFuel/BackOffice/index.php">👥 Utilisateurs</a>
    </div>
</div>
</body>
</html>
