<?php
// services/AdminService.php

require_once __DIR__ . '/../dao/UtilisateurDAO.php';
require_once __DIR__ . '/../dao/FournisseurDAO.php';
require_once __DIR__ . '/../dao/DepartementDAO.php';
require_once __DIR__ . '/../dao/DevisDAO.php';
require_once __DIR__ . '/../dao/BonCommandeDAO.php';
require_once __DIR__ . '/../dao/ColisDAO.php';

class AdminService {

    private UtilisateurDAO $utilisateurDAO;
    private FournisseurDAO $fournisseurDAO;
    private DepartementDAO $deptDAO;
    private DevisDAO       $devisDAO;
    private BonCommandeDAO $bcDAO;
    private ColisDAO       $colisDAO;

    public function __construct() {
        $this->utilisateurDAO = new UtilisateurDAO();
        $this->fournisseurDAO = new FournisseurDAO();
        $this->deptDAO        = new DepartementDAO();
        $this->devisDAO       = new DevisDAO();
        $this->bcDAO          = new BonCommandeDAO();
        $this->colisDAO       = new ColisDAO();
    }

    /* ===== STATS DASHBOARD ===== */

    public function getStats(): array {
        return [
            'utilisateurs'  => $this->utilisateurDAO->count(),
            'devis'         => $this->devisDAO->countByStatut('en_attente'),
            'bons_commande' => $this->bcDAO->count(),
            'colis'         => count($this->colisDAO->findAll()),
            'par_role'      => $this->utilisateurDAO->countParRole(),
            'devis_statuts' => $this->devisDAO->countParStatut(),
            'bc_statuts'    => $this->bcDAO->countParStatut(),
            'colis_statuts' => $this->colisDAO->countByStatut(1),
        ];
    }

    /* ===== UTILISATEURS ===== */

    public function getAllUtilisateurs(): array {
        return $this->utilisateurDAO->findAll();
    }

    public function getRoles(): array {
        return $this->utilisateurDAO->getRoles();
    }

    public function updateUtilisateur(int $id, int $role_id, ?int $departement_id): array {
        $ok = $this->utilisateurDAO->update($id, $role_id, $departement_id);
        return $ok
            ? ['success' => true,  'message' => 'Utilisateur mis à jour.']
            : ['success' => false, 'message' => 'Erreur mise à jour.'];
    }

    /* ===== FOURNISSEURS ===== */

    public function getAllFournisseurs(): array {
        return $this->fournisseurDAO->findAll();
    }

    public function getFournisseurById(int $id): ?array {
        return $this->fournisseurDAO->findById($id);
    }

    public function ajouterFournisseur(array $data): array {
        if (empty($data['nom'])) {
            return ['success' => false, 'message' => 'Nom requis.'];
        }
        $this->fournisseurDAO->insert($data);
        return ['success' => true, 'message' => 'Fournisseur ajouté.'];
    }

    public function updateFournisseur(int $id, array $data): array {
        $this->fournisseurDAO->update($id, $data);
        return ['success' => true, 'message' => 'Fournisseur modifié.'];
    }

    /* ===== DEPARTEMENTS ===== */

    public function getAllDepartements(): array {
        return $this->deptDAO->findAll();
    }

    public function getDepartementById(int $id): ?array {
        return $this->deptDAO->findById($id);
    }

    public function ajouterDepartement(string $nom, float $budget): array {
        if (empty($nom)) {
            return ['success' => false, 'message' => 'Nom requis.'];
        }
        $this->deptDAO->insert($nom, $budget);
        return ['success' => true, 'message' => 'Département ajouté.'];
    }

    public function updateDepartement(int $id, string $nom, float $budget): array {
        $this->deptDAO->update($id, $nom, $budget);
        return ['success' => true, 'message' => 'Département modifié.'];
    }

    /* ===== DEVIS ===== */

    public function getAllDevis(?string $search = null): array {
        return $this->devisDAO->findAll($search);
    }

    /* ===== BONS DE COMMANDE ===== */

    public function getAllBonsCommande(?string $search = null): array {
        return $this->bcDAO->findAll($search);
    }

    /* ===== COLIS ===== */

    public function getAllColis(?string $search = null): array {
        return $search
            ? $this->colisDAO->search($search)
            : $this->colisDAO->findAll();
    }
}
