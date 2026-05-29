<?php
// services/ColisService.php

require_once __DIR__ . '/../dao/ColisDAO.php';

class ColisService {

    private ColisDAO $dao;

    const STATUTS = [
        1 => 'recu_universite',
        2 => 'transfere_iut',
        3 => 'en_attente',
        4 => 'livre'
    ];

    public function __construct() {
        $this->dao = new ColisDAO();
    }

    /* ===== LECTURE ===== */

    public function getAllColis(): array {
        return $this->dao->findAll();
    }

    public function getColisById(int $id): ?Colis {
        return $this->dao->findById($id);
    }

    public function getColisByNumeroSuivi(string $numero_suivi): ?Colis {
        return $this->dao->findByNumeroSuivi($numero_suivi);
    }

    public function getColisByStatut(int $statut_id): array {
        return $this->dao->findByStatut($statut_id);
    }

    public function getColisNonIdentifies(): array {
        return $this->dao->findNonIdentifies();
    }

    public function rechercher(string $motcle): array {
        return $this->dao->search($motcle);
    }

    public function getDerniersColis(int $limit = 10): array {
        return $this->dao->getDerniers($limit);
    }

    public function getStats(): array {
        return [
            'recus'           => $this->dao->countByStatut(2),
            'en_attente'      => $this->dao->countByStatut(3),
            'retires'         => $this->dao->countByStatut(4),
            'non_identifies'  => $this->dao->countNonIdentifies(),
            'recus_aujourdhui'=> $this->dao->countRecusAujourdhui(),
        ];
    }

    /* ===== ACTIONS ===== */

    public function enregistrerColis(array $data): array {
        if (empty($data['bon_commande_id'])) {
            return ['success' => false, 'message' => 'Bon de commande requis.'];
        }

        $colis = new Colis($data);
        $ok = $this->dao->insert($colis);

        return $ok
            ? ['success' => true,  'message' => 'Colis enregistré avec succès.']
            : ['success' => false, 'message' => 'Erreur lors de l\'enregistrement.'];
    }

    public function modifierColis(int $id, array $data): array {
        $colis = $this->dao->findById($id);
        if (!$colis) {
            return ['success' => false, 'message' => 'Colis introuvable.'];
        }

        $updated = new Colis(array_merge($colis->toArray(), $data));
        $ok = $this->dao->update($id, $updated);

        return $ok
            ? ['success' => true,  'message' => 'Colis modifié avec succès.']
            : ['success' => false, 'message' => 'Erreur lors de la modification.'];
    }

    public function changerStatut(int $id, int $statut_id): array {
        if (!isset(self::STATUTS[$statut_id])) {
            return ['success' => false, 'message' => 'Statut invalide.'];
        }

        $colis = $this->dao->findById($id);
        if (!$colis) {
            return ['success' => false, 'message' => 'Colis introuvable.'];
        }

        $this->dao->updateStatut($id, $statut_id);
        return ['success' => true, 'message' => 'Statut mis à jour.'];
    }

    public function confirmerReceptionIUT(int $id): array {
        return $this->changerStatut($id, 2);
    }

    public function marquerRetire(int $id): array {
        $colis = $this->dao->findById($id);
        if (!$colis) {
            return ['success' => false, 'message' => 'Colis introuvable.'];
        }

        $this->dao->marquerRetire($id);
        return ['success' => true, 'message' => 'Colis marqué comme retiré.'];
    }

    /* ===== HISTORIQUE ===== */

    public function getHistorique(int $id_colis): array {
        return $this->dao->getHistorique($id_colis);
    }

    public function getHistoriqueGlobal(): array {
        return $this->dao->getHistoriqueGlobal();
    }
}
