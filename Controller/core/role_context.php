<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sportfuel_current_role() {
    $role = trim((string)($_SESSION['role'] ?? ''));
    if (strcasecmp($role, 'Coach') === 0) {
        return 'Coach';
    }
    if (strcasecmp($role, 'Admin') === 0) {
        return 'Admin';
    }
    return 'Sportif';
}

function sportfuel_is_backoffice_role() {
    $role = sportfuel_current_role();
    return $role === 'Admin' || $role === 'Coach';
}

function sportfuel_canonical_redirect_path($role) {
    if (strcasecmp((string)$role, 'Admin') === 0 || strcasecmp((string)$role, 'Coach') === 0) {
        return '/Esprit-PW-2A19-2526-SportFuel/index.php?page=dashboard';
    }
    return '/Esprit-PW-2A19-2526-SportFuel/index.php?page=home';
}

function sportfuel_legacy_redirect_path($role) {
    if (strcasecmp((string)$role, 'Admin') === 0 || strcasecmp((string)$role, 'Coach') === 0) {
        return '/Esprit-PW-2A19-2526-SportFuel/index.php?page=back&action=listPlans';
    }
    return '/Esprit-PW-2A19-2526-SportFuel/View/users/profil.html';
}
