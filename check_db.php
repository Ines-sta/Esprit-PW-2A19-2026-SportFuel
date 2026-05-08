<?php
require 'c:\xampp\htdocs\SportFuel-Module1\controller\config.php';
require 'c:\xampp\htdocs\SportFuel-Module1\model\Utilisateur.php';

$users = Utilisateur::getAll($pdo);
foreach($users as $u) {
    echo $u->getEmail() . " -> " . $u->getPassword() . "\n";
}
