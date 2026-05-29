<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../controllers/DirecteurController.php';

$controller = new DirecteurController();
$controller->signerDevis();