<?php
require __DIR__ . '/controller/config.php';
require __DIR__ . '/model/Utilisateur.php';

$users = Utilisateur::getAll($pdo);
foreach($users as $u) {
    echo $u->getEmail() . " -> " . $u->getPassword() . "\n";
}
