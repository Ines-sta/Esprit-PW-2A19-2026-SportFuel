<?php
/**
 * Sidebar BackOffice — SportFuel
 */
$page   = $_GET['page']   ?? 'home';
$action = $_GET['action'] ?? '';
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-mark"><span>SF<br>FUEL</span></div>
        <div class="sidebar-logo-text">
            <strong>SportFuel</strong>
            <small>Admin</small>
        </div>
    </div>
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

        <a href="index.php?page=back&action=aiMealGenerator"
           class="sidebar-link <?= ($page === 'back' && $action === 'aiMealGenerator') ? 'active' : '' ?>">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 2v12M2 8h12"/><circle cx="8" cy="8" r="2"/></svg>
            Generateur IA
        </a>

        <a href="index.php?page=back&action=shoppingList"
           class="sidebar-link <?= ($page === 'back' && $action === 'shoppingList') ? 'active' : '' ?>">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="11" rx="1"/><path d="M5 3V2a1 1 0 011-1h4a1 1 0 011 1v1"/></svg>
            Liste de courses
        </a>

        <a href="index.php?page=back&action=comparePlans"
           class="sidebar-link <?= ($page === 'back' && $action === 'comparePlans') ? 'active' : '' ?>">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="6" height="10" rx="1"/><rect x="9" y="3" width="6" height="10" rx="1"/></svg>
            Comparer plans
        </a>

        <a href="index.php?page=plans" class="sidebar-link <?= ($page === 'plans') ? 'active' : '' ?>">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><line x1="5" y1="8" x2="11" y2="8"/><line x1="8" y1="5" x2="8" y2="11"/></svg>
            Vue publique
        </a>
    </nav>
</aside>
