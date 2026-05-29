<?php
// services/DepartementService.php

require_once __DIR__ . '/../dao/DevisDAO.php';
require_once __DIR__ . '/../dao/BonCommandeDAO.php';
require_once __DIR__ . '/../dao/ColisDAO.php';
require_once __DIR__ . '/../dao/FournisseurDAO.php';
require_once __DIR__ . '/../dao/DepartementDAO.php';

class DepartementService {

    private DevisDAO       $devisDAO;
    private BonCommandeDAO $bcDAO;
    private ColisDAO       $colisDAO;
    private FournisseurDAO $fournisseurDAO;
    private DepartementDAO $deptDAO;

    public function __construct() {
        $this->devisDAO       = new DevisDAO();
        $this->bcDAO          = new BonCommandeDAO();
        $this->colisDAO       = new ColisDAO();
        $this->fournisseurDAO = new FournisseurDAO();
        $this->deptDAO        = new DepartementDAO();
    }

    public function getStats(int $departement_id): array {
        $budget = $this->deptDAO->getBudget($departement_id);
        return [
            'budget'         => $budget,
            'derniers_colis' => $this->colisDAO->getDerniers(10),
        ];
    }

    public function getMesDevis(int $createur_id): array {
        return $this->devisDAO->findByCreateur($createur_id);
    }

    public function getMesBonsCommande(int $departement_id): array {
        return $this->bcDAO->findByDepartement($departement_id);
    }

    public function getColisDepartement(int $departement_id): array {
        return $this->colisDAO->findByDepartement($departement_id);
    }

    public function getFournisseurs(): array {
        return $this->fournisseurDAO->findAll();
    }

    public function getBudget(int $departement_id): ?array {
        $budget = $this->deptDAO->getBudget($departement_id);
        if (!$budget) return null;
        $depenses = $this->bcDAO->getDepensesParDepartement($departement_id);
        return array_merge($budget, ['depenses' => $depenses]);
    }

    public function creerDevis(array $data): array {
        if (empty($data['objet']) || empty($data['montant_estime']) || empty($data['fournisseur_id'])) {
            return ['success' => false, 'message' => 'Tous les champs sont requis.'];
        }
        $ok = $this->devisDAO->insert($data);
        return $ok
            ? ['success' => true,  'message' => 'Devis soumis avec succès.']
            : ['success' => false, 'message' => 'Erreur lors de la soumission.'];
    }
}
