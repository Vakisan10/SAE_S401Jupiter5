<?php
// services/DirecteurService.php

require_once __DIR__ . '/../dao/DevisDAO.php';
require_once __DIR__ . '/../dao/BonCommandeDAO.php';

class DirecteurService {

    private DevisDAO       $devisDAO;
    private BonCommandeDAO $bcDAO;

    public function __construct() {
        $this->devisDAO = new DevisDAO();
        $this->bcDAO    = new BonCommandeDAO();
    }

    public function getStats(): array {
        return [
            'devis_a_signer' => $this->devisDAO->countByStatut('valide_finance'),
            'bons_commande'  => $this->bcDAO->count(),
        ];
    }

    public function getDevisAValider(): array {
        return $this->devisDAO->findByStatut('valide_finance');
    }

    public function getDevisComplet(int $id): ?array {
        return $this->devisDAO->findComplet($id);
    }

    public function getTousLesBonsCommande(): array {
        return $this->bcDAO->findAvecDevis();
    }
}
