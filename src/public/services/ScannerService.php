<?php
// services/ScannerService.php

require_once __DIR__ . '/../dao/ColisDAO.php';

class ScannerService {

    private ColisDAO $dao;

    public function __construct() {
        $this->dao = new ColisDAO();
    }

    public function traiterScan(string $qr_content): array {
        $qr_content = trim($qr_content);

        if (empty($qr_content)) {
            return ['success' => false, 'message' => 'QR code vide.'];
        }

        $colis = $this->dao->findByNumeroSuivi($qr_content);

        if (!$colis) {
            return ['success' => false, 'message' => "Aucun colis trouvé pour : $qr_content"];
        }

        return [
            'success' => true,
            'colis'   => $colis->toArray()
        ];
    }
}
