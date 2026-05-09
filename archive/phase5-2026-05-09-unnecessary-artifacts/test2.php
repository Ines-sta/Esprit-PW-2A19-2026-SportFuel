<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/FrontOfficeController.php';

$controller = new FrontOfficeController();
$data = $controller->getData();

print_r($data);
