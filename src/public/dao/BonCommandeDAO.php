<?php
// dao/BonCommandeDAO.php

require_once __DIR__ . '/../models/Model.php';

class BonCommandeDAO {

    private $db;

    public function __construct() {
        $this->db = Model::getModel()->bd;
    }

    /* ===== LECTURE ===== */

    public function findAll(?string $search = null): array {
        $sql = "SELECT b.id_bon_commande, b.numero_commande, b.date_commande,
                       b.montant_estime, b.statut,
                       dep.nom AS departement, f.nom AS fournisseur
                FROM bon_commande b
                LEFT JOIN departement dep ON b.departement_id = dep.id_departement
                LEFT JOIN fournisseur f   ON b.fournisseur_id = f.id_fournisseur";
        $params = [];
        if ($search) {
            $s = "%$search%";
            $sql .= " WHERE b.numero_commande LIKE ? OR b.statut LIKE ? OR dep.nom LIKE ? OR f.nom LIKE ?";
            $params = [$s, $s, $s, $s];
        }
        $sql .= " ORDER BY b.date_commande DESC";
        $req = $this->db->prepare($sql);
        $req->execute($params);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $req = $this->db->prepare("SELECT * FROM bon_commande WHERE id_bon_commande = ?");
        $req->execute([$id]);
        return $req->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByNumero(string $numero): ?array {
        $req = $this->db->prepare("SELECT id_bon_commande, numero_commande FROM bon_commande WHERE numero_commande = ?");
        $req->execute([$numero]);
        return $req->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByDepartement(int $departement_id): array {
        $sql = "SELECT b.id_bon_commande, b.numero_commande, b.date_commande,
                       b.montant_estime, b.statut, f.nom AS fournisseur_nom
                FROM bon_commande b
                JOIN fournisseur f ON b.fournisseur_id = f.id_fournisseur
                WHERE b.departement_id = ?
                ORDER BY b.id_bon_commande DESC";
        $req = $this->db->prepare($sql);
        $req->execute([$departement_id]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findRecents(int $limit = 10): array {
        return $this->db->query(
            "SELECT numero_commande, date_commande, montant_estime, statut
             FROM bon_commande ORDER BY date_commande DESC LIMIT $limit"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAvecDevis(): array {
        $sql = "SELECT b.id_bon_commande, b.numero_commande, b.date_commande,
                       d.objet, d.montant_estime
                FROM bon_commande b
                LEFT JOIN devis d ON b.devis_id = d.id_devis
                ORDER BY b.date_commande DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findInfosParNumero(string $numero): ?array {
        $sql = "SELECT b.id_bon_commande, u.id_utilisateur AS destinataire_id, d.id_departement
                FROM bon_commande b
                LEFT JOIN utilisateur u   ON b.createur_id   = u.id_utilisateur
                LEFT JOIN departement d   ON b.departement_id = d.id_departement
                WHERE b.numero_commande = ?";
        $req = $this->db->prepare($sql);
        $req->execute([$numero]);
        return $req->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ===== STATISTIQUES ===== */

    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM bon_commande")->fetchColumn();
    }

    public function countParStatut(): array {
        return $this->db->query("SELECT statut, COUNT(*) AS total FROM bon_commande GROUP BY statut")
                        ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDepensesParDepartement(int $departement_id): array {
        $req = $this->db->prepare(
            "SELECT numero_commande, date_commande, montant_estime, statut
             FROM bon_commande WHERE departement_id = ? ORDER BY date_commande DESC"
        );
        $req->execute([$departement_id]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===== ECRITURE ===== */

    public function insert(array $data): bool {
        $sql = "INSERT INTO bon_commande
                    (numero_commande, date_commande, montant_estime, fournisseur_id, createur_id, departement_id, devis_id)
                VALUES (?, CURDATE(), ?, ?, ?, ?, ?)";
        $req = $this->db->prepare($sql);
        return $req->execute([
            $data['numero_commande'],
            $data['montant_estime'],
            $data['fournisseur_id'],
            $data['createur_id'],
            $data['departement_id'],
            $data['devis_id']
        ]);
    }
}
