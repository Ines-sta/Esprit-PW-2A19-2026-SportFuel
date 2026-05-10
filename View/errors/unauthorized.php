<?php
$isAuthenticated = !empty($_SESSION['user_email']);
$role = $isAuthenticated ? sportfuel_current_role() : '';
$displayRole = $isAuthenticated ? $role : 'Visiteur';
$message = trim((string)($_SESSION['unauthorized_message'] ?? 'Vous n\'avez pas la permission d\'acceder a cette page.'));
unset($_SESSION['unauthorized_message']);

if (!$isAuthenticated) {
    $targetPath = '/Esprit-PW-2A19-2026-SportFuel/View/auth/connexion.html';
    $targetLabel = 'Aller a la connexion';
} else {
    $targetPath = sportfuel_canonical_redirect_path($role);
    $targetLabel = ($role === 'Admin' || $role === 'Coach')
        ? 'Retourner au tableau de bord'
        : 'Retourner a l\'accueil';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acces non autorise - SportFuel</title>
    <link rel="stylesheet" href="/Esprit-PW-2A19-2026-SportFuel/public/css/style.css">
</head>
<body class="unauthorized-page">
    <main class="unauthorized-shell">
        <section class="unauthorized-card" aria-labelledby="unauthorizedTitle">
            <span class="unauthorized-badge">Securite SportFuel</span>
            <h1 id="unauthorizedTitle">Acces non autorise</h1>
            <p class="unauthorized-subtitle">Cette section est reservee a un autre role.</p>

            <div class="unauthorized-meta">
                <div class="unauthorized-meta-item">
                    <span class="unauthorized-meta-label">Role actif</span>
                    <strong><?php echo htmlspecialchars($displayRole, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="unauthorized-meta-item">
                    <span class="unauthorized-meta-label">Statut</span>
                    <strong>Blocage 403</strong>
                </div>
            </div>

            <p class="unauthorized-message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>

            <div class="unauthorized-actions">
                <a class="btn btn-primary" href="<?php echo htmlspecialchars($targetPath, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($targetLabel, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php if ($isAuthenticated): ?>
                    <a class="btn btn-secondary" href="/Esprit-PW-2A19-2026-SportFuel/index.php?page=auth&action=logout">Se deconnecter</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
