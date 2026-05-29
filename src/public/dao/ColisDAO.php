<?php
// dao/ColisDAO.php

require_once __DIR__ . '/../models/Model.php';
require_once __DIR__ . '/../models/Colis.php';

class ColisDAO {

    private $db;

    public function __construct() {
        $this->db = Model::getModel()->bd;
    }

    /* ===== LECTURE ===== */

    public function findAll(): array {
        $sql = "SELECT c.*, s.libelle AS statut, b.numero_commande, d.nom AS departement
                FROM colis c
                LEFT JOIN bon_commande b ON c.bon_commande_id = b.id_bon_commande
                LEFT JOIN departement d ON b.departement_id = d.id_departement
                JOIN statut_colis s ON c.statut_id = s.id_statut
                ORDER BY c.date_reception DESC";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => new Colis($row), $rows);
    }

    public function findById(int $id): ?Colis {
        $sql = "SELECT c.*, s.libelle AS statut, b.numero_commande, d.nom AS departement
                FROM colis c
                LEFT JOIN bon_commande b ON c.bon_commande_id = b.id_bon_commande
                LEFT JOIN departement d ON b.departement_id = d.id_departement
                JOIN statut_colis s ON c.statut_id = s.id_statut
                WHERE c.id_colis = ?";
        $req = $this->db->prepare($sql);
        $req->execute([$id]);
        $row = $req->fetch(PDO::FETCH_ASSOC);
        return $row ? new Colis($row) : null;
    }

    public function findByNumeroSuivi(string $numero_suivi): ?Colis {
        $sql = "SELECT c.*, s.libelle AS statut, b.numero_commande, d.nom AS departement
                FROM colis c
                LEFT JOIN bon_commande b ON c.bon_commande_id = b.id_bon_commande
                LEFT JOIN departement d ON b.departement_id = d.id_departement
                JOIN statut_colis s ON c.statut_id = s.id_statut
                WHERE c.numero_suivi = ?";
        $req = $this->db->prepare($sql);
        $req->execute([$numero_suivi]);
        $row = $req->fetch(PDO::FETCH_ASSOC);
        return $row ? new Colis($row) : null;
    }

    public function findByStatut(int $statut_id): array {
        $sql = "SELECT c.*, s.libelle AS statut, b.numero_commande, d.nom AS departement
                FROM colis c
                LEFT JOIN bon_commande b ON c.bon_commande_id = b.id_bon_commande
                LEFT JOIN departement d ON b.departement_id = d.id_departement
                JOIN statut_colis s ON c.statut_id = s.id_statut
                WHERE c.statut_id = ?
                ORDER BY c.date_reception DESC";
        $req = $this->db->prepare($sql);
        $req->execute([$statut_id]);
        $rows = $req->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => new Colis($row), $rows);
    }

    public function findNonIdentifies(): array {
        $sql = "SELECT c.*, s.libelle AS statut
                FROM colis c
                JOIN statut_colis s ON c.statut_id = s.id_statut
                WHERE c.statut_id = 1 AND c.destinataire_id IS NULL
                ORDER BY c.date_reception DESC";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => new Colis($row), $rows);
    }

    public function search(string $motcle): array {
        $motcle = "%$motcle%";
        $sql = "SELECT c.*, s.libelle AS statut, b.numero_commande, d.nom AS departement
                FROM colis c
                LEFT JOIN bon_commande b ON c.bon_commande_id = b.id_bon_commande
                LEFT JOIN departement d ON b.departement_id = d.id_departement
                JOIN statut_colis s ON c.statut_id = s.id_statut
                WHERE c.numero_suivi LIKE ? OR b.numero_commande LIKE ?
                OR d.nom LIKE ? OR c.id_colis LIKE ?
                ORDER BY c.date_reception DESC";
        $req = $this->db->prepare($sql);
        $req->execute([$motcle, $motcle, $motcle, $motcle]);
        $rows = $req->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => new Colis($row), $rows);
    }

    /* ===== STATISTIQUES ===== */

    public function countByStatut(int $statut_id): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM colis WHERE statut_id = $statut_id")->fetchColumn();
    }

    public function countNonIdentifies(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM colis WHERE statut_id = 1 AND destinataire_id IS NULL")->fetchColumn();
    }

    public function countRecusAujourdhui(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM colis WHERE DATE(date_reception) = CURDATE()")->fetchColumn();
    }

    public function getDerniers(int $limit = 10): array {
        $sql = "SELECT c.*, s.libelle AS statut, d.nom AS departement
                FROM colis c
                LEFT JOIN bon_commande b ON c.bon_commande_id = b.id_bon_commande
                LEFT JOIN departement d ON b.departement_id = d.id_departement
                JOIN statut_colis s ON c.statut_id = s.id_statut
                ORDER BY c.date_reception DESC LIMIT $limit";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => new Colis($row), $rows);
    }

    /* ===== ECRITURE ===== */

    public function insert(Colis $colis): bool {
        $sql = "INSERT INTO colis (bon_commande_id, numero_suivi, code_barres, destinataire_id, date_reception, statut_id, commentaire, receptionne_par)
                VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)";
        $req = $this->db->prepare($sql);
        $result = $req->execute([
            $colis->getBonCommandeId(),
            $colis->getNumeroSuivi(),
            $colis->getCodeBarres(),
            $colis->getDestinataire(),
            $colis->getStatutId(),
            $colis->getCommentaire(),
            $colis->getReceptionnepar()
        ]);
        if ($result) $this->addHistorique((int)$this->db->lastInsertId(), "Colis créé");
        return $result;
    }

    public function update(int $id, Colis $colis): bool {
        $sql = "UPDATE colis
                SET numero_suivi = ?, bon_commande_id = ?, destinataire_id = ?,
                    statut_id = ?, commentaire = ?
                WHERE id_colis = ?";
        $req = $this->db->prepare($sql);
        $result = $req->execute([
            $colis->getNumeroSuivi(),
            $colis->getBonCommandeId(),
            $colis->getDestinataire(),
            $colis->getStatutId(),
            $colis->getCommentaire(),
            $id
        ]);
        if ($result) $this->addHistorique($id, "Colis modifié");
        return $result;
    }

    public function updateStatut(int $id, int $statut_id): bool {
        $req = $this->db->prepare("UPDATE colis SET statut_id = ? WHERE id_colis = ?");
        $result = $req->execute([$statut_id, $id]);
        if ($result) $this->addHistorique($id, "Statut modifié en $statut_id");
        return $result;
    }

    public function marquerRetire(int $id): bool {
        $req = $this->db->prepare("UPDATE colis SET statut_id = 4, date_retrait = NOW() WHERE id_colis = ?");
        $result = $req->execute([$id]);
        if ($result) $this->addHistorique($id, "Colis retiré");
        return $result;
    }

    /* ===== HISTORIQUE ===== */

    public function getHistorique(int $id_colis): array {
        $req = $this->db->prepare("SELECT * FROM historique_colis WHERE id_colis = ? ORDER BY date_action DESC");
        $req->execute([$id_colis]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistoriqueGlobal(): array {
        $sql = "SELECT h.*, c.numero_suivi, b.numero_commande
                FROM historique_colis h
                LEFT JOIN colis c ON h.id_colis = c.id_colis
                LEFT JOIN bon_commande b ON c.bon_commande_id = b.id_bon_commande
                ORDER BY h.date_action DESC LIMIT 200";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function addHistorique(int $id_colis, string $action): void {
        $req = $this->db->prepare("INSERT INTO historique_colis (id_colis, action, date_action) VALUES (?, ?, NOW())");
        $req->execute([$id_colis, $action]);
    }
}
