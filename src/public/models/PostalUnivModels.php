<?php
require_once __DIR__ . '/Model.php';

class PostalUnivModels {

    private $db;

    public function __construct() {
        $this->db = Model::getModel()->bd;
    }

    public function getColisRecus() {
        return $this->db->query("SELECT COUNT(*) FROM colis")
            ->fetchColumn();
    }

    public function getColisATransferer() {
        return $this->db->query("SELECT COUNT(*) FROM colis WHERE statut_id = 1")
            ->fetchColumn();
    }

    public function getColisTransferes() {
        return $this->db->query("SELECT COUNT(*) FROM colis WHERE statut_id = 2")
            ->fetchColumn();
    }

    public function getColisNonIdentifies() {
        return $this->db->query("SELECT COUNT(*) FROM colis WHERE statut_id = 4")
            ->fetchColumn();
    }

    public function getDerniersColis() {
        return $this->db->query("
            SELECT
                c.id_colis,
                c.numero_suivi,
                c.date_reception,
                s.libelle AS statut
            FROM colis c
            JOIN statut_colis s ON c.statut_id = s.id_statut
            ORDER BY c.date_reception DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
    }


    public function ajouterColisUniversite($data) {

        // 1️⃣ Trouver le bon de commande
        $req = $this->db->prepare("
            SELECT id_bon_commande
            FROM bon_commande
            WHERE numero_commande = ?
        ");
        $req->execute([$data["numero_commande"]]);
        $bc = $req->fetch(PDO::FETCH_ASSOC);

        // 2️⃣ Si BC introuvable → NON IDENTIFIÉ
        if (!$bc) {
            $sql = "
                INSERT INTO colis (
                    bon_commande_id,
                    numero_suivi,
                    date_reception,
                    statut_id,
                    commentaire
                )
                VALUES (NULL, ?, NOW(), 3, ?)
            ";

            $req = $this->db->prepare($sql);
            return $req->execute([
                $data["numero_suivi"],
                $data["commentaire"]
            ]);
        }

        // 3️⃣ Sinon → reçu à l’université
        $sql = "
            INSERT INTO colis (
                bon_commande_id,
                numero_suivi,
                date_reception,
                statut_id,
                commentaire
            )
            VALUES (?, ?, NOW(), 1, ?)
        ";

        $req = $this->db->prepare($sql);
        return $req->execute([
            $bc["id_bon_commande"],
            $data["numero_suivi"],
            $data["commentaire"]
        ]);
    }


    public function getTousLesColis() {
        $sql = "
            SELECT
                c.id_colis,
                c.numero_suivi,
                c.statut_id,
                b.numero_commande,
                d.nom AS departement,
                s.libelle AS statut,
                c.date_reception
            FROM colis c
            LEFT JOIN bon_commande b ON c.bon_commande_id = b.id_bon_commande
            LEFT JOIN departement d ON b.departement_id = d.id_departement
            JOIN statut_colis s ON c.statut_id = s.id_statut
            ORDER BY c.date_reception DESC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }


    public function transfererVersIUT($id_colis) {
        $sql = "
            UPDATE colis
            SET statut_id = 2
            WHERE id_colis = ?
        ";
        $req = $this->db->prepare($sql);
        return $req->execute([$id_colis]);
    }

    public function getColisNonIdentifiesListe() {
        $sql = "
            SELECT
                c.id_colis,
                c.numero_suivi,
                c.date_reception,
                s.libelle AS statut
            FROM colis c
            JOIN statut_colis s ON c.statut_id = s.id_statut
            WHERE c.statut_id = 3
            ORDER BY c.date_reception DESC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }



    public function getHistorique() {

        $sql = "
            SELECT
                h.date_action,
                c.id_colis,
                c.numero_suivi,
                b.numero_commande,
                d.nom AS departement,
                s.libelle AS statut,
                h.action
            FROM historique_colis h
            JOIN colis c ON h.id_colis = c.id_colis
            LEFT JOIN bon_commande b ON c.bon_commande_id = b.id_bon_commande
            LEFT JOIN departement d ON b.departement_id = d.id_departement
            LEFT JOIN statut_colis s ON c.statut_id = s.id_statut
            ORDER BY h.date_action DESC
            LIMIT 200
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

}
