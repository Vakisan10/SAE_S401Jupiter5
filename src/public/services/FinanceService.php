<?php
// services/FinanceService.php

require_once __DIR__ . '/../dao/DevisDAO.php';
require_once __DIR__ . '/../dao/BonCommandeDAO.php';
require_once __DIR__ . '/../dao/DepartementDAO.php';

class FinanceService {

    private DevisDAO       $devisDAO;
    private BonCommandeDAO $bcDAO;
    private DepartementDAO $deptDAO;

    public function __construct() {
        $this->devisDAO = new DevisDAO();
        $this->bcDAO    = new BonCommandeDAO();
        $this->deptDAO  = new DepartementDAO();
    }

    public function getStats(): array {
        return [
            'devis_en_attente' => $this->devisDAO->countByStatut('en_attente'),
            'bons_commande'    => $this->bcDAO->count(),
            'budgets'          => $this->deptDAO->getAllBudgets(),
        ];
    }

    public function getDevisAVerifier(): array {
        return $this->devisDAO->findByStatut('en_attente');
    }

    public function getTousLesBonsCommande(): array {
        return $this->bcDAO->findAll();
    }

    public function getBudgetsDepartements(): array {
        return $this->deptDAO->getAllBudgets();
    }

    public function getDevisComplet(int $id): ?array {
        return $this->devisDAO->findComplet($id);
    }
}
