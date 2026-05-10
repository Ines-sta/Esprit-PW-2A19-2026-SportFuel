<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current user info
$currentUserName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['user_email']) ? explode('@', $_SESSION['user_email'])[0] : 'SF');
$userInitials = strtoupper(substr($currentUserName, 0, 2));

// Active navbar item - set via $navbarActive variable in calling page
$navbarActive = $navbarActive ?? '';
if ($navbarActive === '') {
    $page = (string)($_GET['page'] ?? 'home');
    if ($page === 'home') {
        $navbarActive = 'dashboard';
    } elseif ($page === 'plans' || $page === 'detail') {
        $navbarActive = 'plans';
    } elseif ($page === 'coach') {
        $navbarActive = 'coach';
    } elseif ($page === 'training') {
        $navbarActive = 'training';
    } elseif ($page === 'courses') {
        $navbarActive = 'courses';
    } elseif ($page === 'aliments') {
        $navbarActive = 'aliments';
    }
}
?>
<nav class="navbar">
    <a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=home" class="navbar-brand">
        <div class="navbar-logo">SF</div>
        <span>Sport<em>Fuel</em></span>
    </a>
    <ul class="navbar-links">
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=home" class="<?php echo $navbarActive === 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=plans" class="<?php echo $navbarActive === 'plans' ? 'active' : ''; ?>">Mon plan</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=coach" class="<?php echo $navbarActive === 'coach' ? 'active' : ''; ?>">Coach</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=training" class="<?php echo $navbarActive === 'training' ? 'active' : ''; ?>">Entraînements</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=courses" class="<?php echo $navbarActive === 'courses' ? 'active' : ''; ?>">Courses</a></li>
        <li><a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=aliments" class="<?php echo $navbarActive === 'aliments' ? 'active' : ''; ?>">Aliments</a></li>
    </ul>
    <div class="navbar-actions">
        <a href="/Esprit-PW-2A19-2526-SportFuel/index.php?page=auth&amp;action=logout" class="navbar-logout">Logout</a>
        <div class="navbar-user"><?php echo htmlspecialchars($userInitials); ?></div>
    </div>
</nav>
