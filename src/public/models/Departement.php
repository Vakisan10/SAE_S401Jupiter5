<?php
// models/Departement.php

class Departement {

    private int     $id_departement;
    private string  $nom;
    private ?string $telephone;
    private int     $budget_total;
    private int     $budget_utilise;

    public function __construct(array $dico) {
        $this->id_departement = $dico['id_departement'] ?? 0;
        $this->nom            = $dico['nom'];
        $this->telephone      = $dico['telephone']      ?? null;
        $this->budget_total   = $dico['budget_total']   ?? 0;
        $this->budget_utilise = $dico['budget_utilise'] ?? 0;
    }

    public function getIdDepartement(): int    { return $this->id_departement; }
    public function getNom(): string           { return $this->nom; }
    public function getTelephone(): ?string    { return $this->telephone; }
    public function getBudgetTotal(): int      { return $this->budget_total; }
    public function getBudgetUtilise(): int    { return $this->budget_utilise; }
    public function getBudgetRestant(): int    { return $this->budget_total - $this->budget_utilise; }

    public function setNom(string $nom): void              { $this->nom = $nom; }
    public function setBudgetTotal(int $budget): void      { $this->budget_total = $budget; }
    public function setBudgetUtilise(int $budget): void    { $this->budget_utilise = $budget; }

    public function toArray(): array {
        return [
            'id_departement' => $this->id_departement,
            'nom'            => $this->nom,
            'telephone'      => $this->telephone,
            'budget_total'   => $this->budget_total,
            'budget_utilise' => $this->budget_utilise,
        ];
    }
}
