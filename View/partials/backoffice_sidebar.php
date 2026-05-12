<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/avatar.php';

$roleLabel = strtoupper(trim((string)($_SESSION['role'] ?? 'ADMIN')));
$isAdminRole = ($roleLabel === 'ADMIN');
$sidebarActive = $sidebarActive ?? '';
$currentUserName = isset($_SESSION['user_nom'])
    ? (string)$_SESSION['user_nom']
    : (isset($_SESSION['user_email']) ? explode('@', (string)$_SESSION['user_email'])[0] : 'SportFuel');
$currentUserPhoto = (string)($_SESSION['user_photo'] ?? '');
?>
<aside class="sidebar">
    <div class="sidebar-profile-head">
        <a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=profil" class="sidebar-profile-chip sidebar-profile-link" title="Mon profil">
            <?php echo sportfuel_avatar_markup($currentUserName, $currentUserPhoto, 'sidebar-profile-avatar'); ?>
            <div>
                <div class="sidebar-profile-name"><?php echo htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="sidebar-role"><?php echo htmlspecialchars($roleLabel); ?></div>
            </div>
        </a>
        <a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=auth&action=logout" class="sidebar-logout-icon" title="Déconnexion" aria-label="Déconnexion">⏻</a>
    </div>

    <ul class="sidebar-menu">
        <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=dashboard" class="<?php echo $sidebarActive === 'dashboard' ? 'active' : ''; ?>"><span class="icon">📊</span> Dashboard</a></li>
    </ul>
    <div class="sidebar-section">Modules</div>
    <ul class="sidebar-menu">
        <?php if ($isAdminRole): ?>
            <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=users" class="<?php echo $sidebarActive === 'users' ? 'active' : ''; ?>"><span class="icon">👥</span> Utilisateurs</a></li>
        <?php endif; ?>

        <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=back&action=listPlans" class="<?php echo $sidebarActive === 'plans' ? 'active' : ''; ?>"><span class="icon">🍽️</span> Plans alimentaires</a></li>
        <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=back&action=listRepas" class="<?php echo $sidebarActive === 'repas' ? 'active' : ''; ?>"><span class="icon">🍴</span> Liste des repas</a></li>
        <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=plans&action=shopping_list" class="<?php echo $sidebarActive === 'shopping' ? 'active' : ''; ?>"><span class="icon">🛒</span> Liste de courses</a></li>
        <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=plans&action=ai_generator" class="<?php echo $sidebarActive === 'ai' ? 'active' : ''; ?>"><span class="icon">🤖</span> Générateur IA</a></li>
        <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=plans&action=compare" class="<?php echo $sidebarActive === 'compare' ? 'active' : ''; ?>"><span class="icon">⚖️</span> Comparer plans</a></li>
        <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=training" class="<?php echo $sidebarActive === 'training' ? 'active' : ''; ?>"><span class="icon">🏋️</span> Entraînements</a></li>
        <?php if ($isAdminRole): ?>
            <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=aliments" class="<?php echo $sidebarActive === 'aliments' ? 'active' : ''; ?>"><span class="icon">🥗</span> Aliments</a></li>
            <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=courses" class="<?php echo $sidebarActive === 'courses' ? 'active' : ''; ?>"><span class="icon">🛒</span> Listes de courses</a></li>
        <?php endif; ?>
        <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=coach" class="<?php echo $sidebarActive === 'coach' ? 'active' : ''; ?>"><span class="icon">🤝</span> Publications & Suivi</a></li>
        <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=coach&amp;focus=entrainement" class="<?php echo $sidebarActive === 'coach-training' ? 'active' : ''; ?>"><span class="icon">🏋️</span> Demandes entraînement</a></li>
        <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=coach&amp;focus=nutrition" class="<?php echo $sidebarActive === 'coach-nutrition' ? 'active' : ''; ?>"><span class="icon">🥗</span> Demandes nutrition</a></li>
    </ul>

    <div class="sidebar-footer">
        <a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=dashboard" class="sidebar-brand sidebar-brand-bottom" title="SportFuel">
            <div class="sidebar-logo">SF</div>
            <span>Sport<em>Fuel</em></span>
        </a>
    </div>
</aside>
