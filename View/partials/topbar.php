<?php
/**
 * Topbar FrontOffice — SportFuel
 */
$page = $_GET['page'] ?? 'home';
?>
<header class="topbar">
    <div class="topbar-logo">
        <div class="topbar-logo-mark"><span>SF<br>FUEL</span></div>
        <strong>SportFuel</strong>
    </div>
    <ul class="topbar-nav">
        <li><a href="index.php" class="<?= $page === 'home' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="index.php?page=plans" class="<?= $page === 'plans' ? 'active' : '' ?>">Mon plan</a></li>
        <li><a href="index.php?page=back&action=listPlans" class="<?= $page === 'back' ? 'active' : '' ?>">BackOffice</a></li>
    </ul>
    <div class="topbar-avatar">IN</div>
</header>
