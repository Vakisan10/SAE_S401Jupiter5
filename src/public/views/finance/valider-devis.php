<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . "/../controllers/FinanceController.php";

$controller = new FinanceController();
$controller->validerDevis();