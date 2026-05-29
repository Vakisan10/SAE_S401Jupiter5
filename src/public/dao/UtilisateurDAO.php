<?php
// dao/UtilisateurDAO.php

require_once __DIR__ . '/../core/Database.php';

class UtilisateurDAO {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->bd;
    }

    public function findAll(): array {
        $sql = "SELECT u.id_utilisateur, u.uid_cas, u.fullName, u.email,
                       r.libelle AS role, u.role_id,
                       d.nom AS departement, u.departement_id
                FROM utilisateur u
                JOIN role r ON u.role_id = r.id_role
                LEFT JOIN departement d ON u.departement_id = d.id_departement
                ORDER BY u.fullName";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $req = $this->db->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
        $req->execute([$id]);
        return $req->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getRoles(): array {
        return $this->db->query("SELECT id_role, libelle FROM role")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
    }

    public function countParRole(): array {
        $sql = "SELECT r.libelle, COUNT(u.id_utilisateur) AS total
                FROM role r
                LEFT JOIN utilisateur u ON u.role_id = r.id_role
                GROUP BY r.libelle";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(int $id, int $role_id, ?int $departement_id): bool {
        $req = $this->db->prepare(
            "UPDATE utilisateur SET role_id = ?, departement_id = ? WHERE id_utilisateur = ?"
        );
        return $req->execute([$role_id, $departement_id, $id]);
    }
}
