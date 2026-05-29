<?php
// dao/FournisseurDAO.php

require_once __DIR__ . '/../core/Database.php';

class FournisseurDAO {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->bd;
    }

    public function findAll(): array {
        return $this->db->query("SELECT * FROM fournisseur ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $req = $this->db->prepare("SELECT * FROM fournisseur WHERE id_fournisseur = ?");
        $req->execute([$id]);
        return $req->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function insert(array $data): bool {
        $req = $this->db->prepare(
            "INSERT INTO fournisseur (nom, contact_nom, contact_email, contact_telephone)
             VALUES (?, ?, ?, ?)"
        );
        return $req->execute([
            $data['nom'],
            $data['contact_nom'],
            $data['contact_email'],
            $data['contact_telephone']
        ]);
    }

    public function update(int $id, array $data): bool {
        $req = $this->db->prepare(
            "UPDATE fournisseur SET nom = ?, contact_nom = ?, contact_email = ?, contact_telephone = ?
             WHERE id_fournisseur = ?"
        );
        return $req->execute([
            $data['nom'],
            $data['contact_nom'],
            $data['contact_email'],
            $data['contact_telephone'],
            $id
        ]);
    }
}
