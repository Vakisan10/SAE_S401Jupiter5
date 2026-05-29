<?php
// services/DevisService.php

require_once __DIR__ . '/../dao/DevisDAO.php';
require_once __DIR__ . '/../dao/BonCommandeDAO.php';
require_once __DIR__ . '/../dao/DepartementDAO.php';

class DevisService {

    private DevisDAO       $devisDAO;
    private BonCommandeDAO $bcDAO;
    private DepartementDAO $deptDAO;

    public function __construct() {
        $this->devisDAO = new DevisDAO();
        $this->bcDAO    = new BonCommandeDAO();
        $this->deptDAO  = new DepartementDAO();
    }

    public function getAllDevis(?string $search = null): array {
        return $this->devisDAO->findAll($search);
    }

    public function getDevisById(int $id): ?array {
        return $this->devisDAO->findById($id);
    }

    public function getDevisComplet(int $id): ?array {
        return $this->devisDAO->findComplet($id);
    }

    public function getDevisByStatut(string $statut): array {
        return $this->devisDAO->findByStatut($statut);
    }

    public function getMesDevis(int $createur_id): array {
        return $this->devisDAO->findByCreateur($createur_id);
    }

    public function creerDevis(array $data): array {
        if (empty($data['objet']) || empty($data['montant_estime']) || empty($data['fournisseur_id'])) {
            return ['success' => false, 'message' => 'Tous les champs sont requis.'];
        }
        $ok = $this->devisDAO->insert($data);
        return $ok
            ? ['success' => true,  'message' => 'Devis créé avec succès.']
            : ['success' => false, 'message' => 'Erreur lors de la création.'];
    }

    /**
     * Finance : valider un devis + déduire du budget département
     */
    public function validerDevis(int $id): array {
        $devis = $this->devisDAO->findComplet($id);
        if (!$devis) {
            return ['success' => false, 'message' => 'Devis introuvable.'];
        }

        $this->devisDAO->updateStatut($id, 'valide_finance');
        $this->deptDAO->incrementBudgetUtilise(
            $devis['departement_id'] ?? 0,
            $devis['montant_estime']
        );

        return ['success' => true, 'message' => 'Devis validé.'];
    }

    /**
     * Finance : rejeter un devis
     */
    public function rejeterDevis(int $id): array {
        $devis = $this->devisDAO->findById($id);
        if (!$devis) {
            return ['success' => false, 'message' => 'Devis introuvable.'];
        }
        $this->devisDAO->updateStatut($id, 'rejete_finance');
        return ['success' => true, 'message' => 'Devis rejeté.'];
    }

    /**
     * Directeur : signer un devis + créer le bon de commande
     */
    public function signerDevis(int $id_devis): array {
        $devis = $this->devisDAO->findComplet($id_devis);
        if (!$devis) {
            return ['success' => false, 'message' => 'Devis introuvable.'];
        }

        if ($this->devisDAO->bonCommandeExistePourDevis($id_devis)) {
            return ['success' => false, 'message' => 'Un bon de commande existe déjà pour ce devis.'];
        }

        $this->devisDAO->updateStatut($id_devis, 'signe_directeur');

        $numeroBC = 'BC-' . date('Y') . '-' . str_pad($id_devis, 3, '0', STR_PAD_LEFT);
        $this->bcDAO->insert([
            'numero_commande' => $numeroBC,
            'montant_estime'  => $devis['montant_estime'],
            'fournisseur_id'  => $devis['fournisseur_id'] ?? null,
            'createur_id'     => $devis['createur_id']    ?? null,
            'departement_id'  => $devis['departement_id'] ?? null,
            'devis_id'        => $id_devis
        ]);

        return ['success' => true, 'message' => 'Devis signé, bon de commande créé.'];
    }
}
