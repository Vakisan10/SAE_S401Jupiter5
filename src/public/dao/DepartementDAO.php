<?php
// dao/DepartementDAO.php

require_once __DIR__ . '/../core/Database.php';

class DepartementDAO {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->bd;
    }

    public function findAll(): array {
        return $this->db->query(
            "SELECT id_departement, nom, budget_total, budget_utilise FROM departement ORDER BY nom"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $req = $this->db->prepare("SELECT * FROM departement WHERE id_departement = ?");
        $req->execute([$id]);
        return $req->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findNom(int $id): ?string {
        $req = $this->db->prepare("SELECT nom FROM departement WHERE id_departement = ?");
        $req->execute([$id]);
        return $req->fetchColumn() ?: null;
    }

    public function getBudget(int $id): ?array {
        $req = $this->db->prepare(
            "SELECT budget_total, budget_utilise FROM departement WHERE id_departement = ?"
        );
        $req->execute([$id]);
        return $req->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAllBudgets(): array {
        return $this->db->query(
            "SELECT nom, budget_total, budget_utilise,
                    (budget_total - budget_utilise) AS budget_restant
             FROM departement ORDER BY nom"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(string $nom, float $budget): bool {
        $req = $this->db->prepare(
            "INSERT INTO departement (nom, budget_total, budget_utilise) VALUES (?, ?, 0)"
        );
        return $req->execute([$nom, $budget]);
    }

    public function update(int $id, string $nom, float $budget): bool {
        $req = $this->db->prepare(
            "UPDATE departement SET nom = ?, budget_total = ? WHERE id_departement = ?"
        );
        return $req->execute([$nom, $budget, $id]);
    }

    public function incrementBudgetUtilise(int $id, float $montant): bool {
        $req = $this->db->prepare(
            "UPDATE departement SET budget_utilise = budget_utilise + ? WHERE id_departement = ?"
        );
        return $req->execute([$montant, $id]);
    }
}
