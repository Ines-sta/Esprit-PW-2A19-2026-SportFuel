<?php
require_once __DIR__ . '/../core/role_context.php';

$mode = trim((string)($_GET['mode'] ?? ''));

if ($mode === 'front') {
    require __DIR__ . '/course_user_controller.php';
    return;
}

if ($mode === 'back') {
    if (!sportfuel_is_backoffice_role()) {
        http_response_code(403);
        echo 'Acces refuse: role BackOffice requis.';
        exit;
    }
    require __DIR__ . '/course_admin_controller.php';
    return;
}

if (sportfuel_is_backoffice_role()) {
    require __DIR__ . '/course_admin_controller.php';
} else {
    require __DIR__ . '/course_user_controller.php';
}
