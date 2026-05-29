<?php
// dao/DevisDAO.php

require_once __DIR__ . '/../core/Database.php';

class DevisDAO {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->bd;
    }

    /* ===== LECTURE ===== */

    public function findAll(?string $search = null): array {
        $sql = "SELECT d.id_devis, d.objet, d.montant_estime, d.statut, d.date_demande,
                       dep.nom AS departement, f.nom AS fournisseur
                FROM devis d
                LEFT JOIN utilisateur u   ON d.createur_id   = u.id_utilisateur
                LEFT JOIN departement dep ON u.departement_id = dep.id_departement
                LEFT JOIN fournisseur f   ON d.fournisseur_id = f.id_fournisseur";
        $params = [];
        if ($search) {
            $s = "%$search%";
            $sql .= " WHERE d.objet LIKE ? OR d.statut LIKE ? OR dep.nom LIKE ? OR f.nom LIKE ?";
            $params = [$s, $s, $s, $s];
        }
        $sql .= " ORDER BY d.date_demande DESC";
        $req = $this->db->prepare($sql);
        $req->execute($params);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $req = $this->db->prepare("SELECT * FROM devis WHERE id_devis = ?");
        $req->execute([$id]);
        return $req->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findComplet(int $id): ?array {
        $sql = "SELECT d.id_devis, d.date_demande, d.objet, d.montant_estime, d.statut,
                       f.nom AS fournisseur_nom, f.contact_nom, f.contact_email, f.contact_telephone,
                       u.fullName AS demandeur_nom, u.email AS demandeur_email,
                       dep.nom AS departement_nom, dep.budget_total, dep.budget_utilise
                FROM devis d
                LEFT JOIN fournisseur f   ON d.fournisseur_id = f.id_fournisseur
                LEFT JOIN utilisateur u   ON d.createur_id   = u.id_utilisateur
                LEFT JOIN departement dep ON u.departement_id = dep.id_departement
                WHERE d.id_devis = ?";
        $req = $this->db->prepare($sql);
        $req->execute([$id]);
        return $req->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByStatut(string $statut): array {
        $sql = "SELECT d.id_devis, d.objet, d.montant_estime, d.date_demande,
                       dep.nom AS departement
                FROM devis d
                LEFT JOIN utilisateur u   ON d.createur_id   = u.id_utilisateur
                LEFT JOIN departement dep ON u.departement_id = dep.id_departement
                WHERE d.statut = ?
                ORDER BY d.date_demande DESC";
        $req = $this->db->prepare($sql);
        $req->execute([$statut]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByCreateur(int $createur_id): array {
        $sql = "SELECT d.id_devis, d.objet, d.montant_estime, d.date_demande, d.statut,
                       f.nom AS fournisseur_nom
                FROM devis d
                JOIN fournisseur f ON d.fournisseur_id = f.id_fournisseur
                WHERE d.createur_id = ?
                ORDER BY d.id_devis DESC";
        $req = $this->db->prepare($sql);
        $req->execute([$createur_id]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPdfById(int $id): ?string {
        $req = $this->db->prepare("SELECT fichier_pdf FROM devis WHERE id_devis = ?");
        $req->execute([$id]);
        return $req->fetchColumn() ?: null;
    }

    /* ===== STATISTIQUES ===== */

    public function countByStatut(string $statut): int {
        $req = $this->db->prepare("SELECT COUNT(*) FROM devis WHERE statut = ?");
        $req->execute([$statut]);
        return (int) $req->fetchColumn();
    }

    public function countParStatut(): array {
        return $this->db->query("SELECT statut, COUNT(*) AS total FROM devis GROUP BY statut")
                        ->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===== ECRITURE ===== */

    public function insert(array $data): bool {
        $sql = "INSERT INTO devis (date_demande, objet, montant_estime, statut, fournisseur_id, createur_id)
                VALUES (CURDATE(), ?, ?, 'en_attente', ?, ?)";
        $req = $this->db->prepare($sql);
        return $req->execute([
            $data['objet'],
            $data['montant_estime'],
            $data['fournisseur_id'],
            $data['createur_id']
        ]);
    }

    public function updateStatut(int $id, string $statut): bool {
        $req = $this->db->prepare("UPDATE devis SET statut = ? WHERE id_devis = ?");
        return $req->execute([$statut, $id]);
    }

    public function bonCommandeExistePourDevis(int $id_devis): bool {
        $req = $this->db->prepare("SELECT COUNT(*) FROM bon_commande WHERE devis_id = ?");
        $req->execute([$id_devis]);
        return (int) $req->fetchColumn() > 0;
    }
}
