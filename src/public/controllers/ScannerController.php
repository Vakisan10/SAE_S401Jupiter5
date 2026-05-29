<?php
// controllers/ScannerController.php

require_once __DIR__ . '/../services/ScannerService.php';

class ScannerController {

    private ScannerService $service;

    public function __construct() {
        $this->service = new ScannerService();
    }

    // GET /scanner
    public function index(): void {
        require __DIR__ . '/../views/scanner/scan.php';
    }

    // POST /scanner/process
    public function process(): void {
        header('Content-Type: application/json');

        $data       = json_decode(file_get_contents('php://input'), true);
        $qr_content = $data['qr_content'] ?? '';

        $result = $this->service->traiterScan($qr_content);
        echo json_encode($result);
    }
}
