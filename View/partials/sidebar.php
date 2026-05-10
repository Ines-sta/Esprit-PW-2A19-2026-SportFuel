<?php
/**
 * Sidebar BackOffice — SportFuel
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/avatar.php';
$page   = $_GET['page']   ?? 'home';
$action = $_GET['action'] ?? '';
$currentUserName = isset($_SESSION['user_nom'])
    ? (string)$_SESSION['user_nom']
    : (isset($_SESSION['user_email']) ? explode('@', (string)$_SESSION['user_email'])[0] : 'SportFuel');
$currentUserPhoto = (string)($_SESSION['user_photo'] ?? '');
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-mark"><span>SF<br>FUEL</span></div>
        <div class="sidebar-logo-text">
            <strong>SportFuel</strong>
            <small>Admin</small>
        </div>
    </div>
    <a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=profil" class="sidebar-profile-chip sidebar-profile-link" style="margin: 2px 16px 10px;" title="Mon profil">
        <?php echo sportfuel_avatar_markup($currentUserName, $currentUserPhoto, 'sidebar-profile-avatar'); ?>
        <div class="sidebar-profile-name"><?php echo htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8'); ?></div>
    </a>
    <nav class="sidebar-nav">
        <a href="index.php?page=back&action=listPlans"
           class="sidebar-link <?= ($page === 'back' && $action === 'listPlans') ? 'active' : '' ?>">
            <svg viewBox="0 0 16 16" fill="currentColor"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
            Dashboard
        </a>

        <div class="sidebar-section-label">Modules</div>

        <a href="index.php?page=back&action=listPlans"
           class="sidebar-link <?= ($page === 'back' && in_array($action, ['listPlans','addPlan','updatePlan'])) ? 'active' : '' ?>">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="2" width="12" height="12" rx="1.5"/><line x1="5" y1="6" x2="11" y2="6"/><line x1="5" y1="9" x2="9" y2="9"/></svg>
            Plans alimentaires
        </a>

        <a href="index.php?page=back&action=listRepas"
           class="sidebar-link <?= ($page === 'back' && in_array($action, ['listRepas','addRepas','updateRepas'])) ? 'active' : '' ?>">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><line x1="8" y1="4" x2="8" y2="8"/><line x1="8" y1="8" x2="11" y2="10"/></svg>
            Repas
        </a>

        <div class="sidebar-section-label">General</div>

        <a href="index.php?page=plans" class="sidebar-link <?= ($page === 'plans') ? 'active' : '' ?>">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><line x1="5" y1="8" x2="11" y2="8"/><line x1="8" y1="5" x2="8" y2="11"/></svg>
            Vue publique
        </a>

        <div class="sidebar-section-label">Autres modules</div>

        <a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=aliments" class="sidebar-link">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/></svg>
            Aliments
        </a>
        <a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=courses" class="sidebar-link">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="10" height="10" rx="1"/></svg>
            Courses
        </a>
        <a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=training" class="sidebar-link">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="2" y1="8" x2="14" y2="8"/><line x1="8" y1="2" x2="8" y2="14"/></svg>
            Entraînements
        </a>
        <a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=coach" class="sidebar-link">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="6" cy="6" r="4"/><path d="M10 10 L14 14"/></svg>
            Espace coach
        </a>
    </nav>
</aside>
