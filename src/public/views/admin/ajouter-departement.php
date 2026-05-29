<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

$controller = new AdminController();
$controller->ajouterDepartement();