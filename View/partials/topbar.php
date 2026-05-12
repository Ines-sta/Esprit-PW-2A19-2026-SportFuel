<?php
/**
 * Topbar FrontOffice — SportFuel
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/avatar.php';
$page = $_GET['page'] ?? 'home';
$currentUserName = isset($_SESSION['user_nom'])
    ? (string)$_SESSION['user_nom']
    : (isset($_SESSION['user_email']) ? explode('@', (string)$_SESSION['user_email'])[0] : 'SportFuel');
$currentUserPhoto = (string)($_SESSION['user_photo'] ?? '');
?>
<header class="topbar">
    <div class="topbar-logo">
        <div class="topbar-logo-mark"><span>SF<br>FUEL</span></div>
        <strong>SportFuel</strong>
    </div>
    <ul class="topbar-nav">
        <li><a href="index.php" class="<?= $page === 'home' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="index.php?page=plans" class="<?= $page === 'plans' ? 'active' : '' ?>">Mon plan</a></li>
        <li><a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=coach" class="<?= in_array($page, ['back', 'coach'], true) ? 'active' : '' ?>">BackOffice</a></li>
    </ul>
    <a href="/Esprit-PW-2A19-2026-SportFuel-main/index.php?page=profil" class="topbar-profile-link" title="Mon profil">
        <?php echo sportfuel_avatar_markup($currentUserName, $currentUserPhoto, 'topbar-avatar'); ?>
        <span class="topbar-profile-name"><?php echo htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8'); ?></span>
    </a>
</header>
