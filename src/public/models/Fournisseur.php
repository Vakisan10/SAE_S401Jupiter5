<?php
// models/Fournisseur.php

class Fournisseur {

    private int     $id_fournisseur;
    private string  $nom;
    private ?string $contact_nom;
    private ?string $contact_email;
    private ?string $contact_telephone;

    public function __construct(array $dico) {
        $this->id_fournisseur    = $dico['id_fournisseur']    ?? 0;
        $this->nom               = $dico['nom'];
        $this->contact_nom       = $dico['contact_nom']       ?? null;
        $this->contact_email     = $dico['contact_email']     ?? null;
        $this->contact_telephone = $dico['contact_telephone'] ?? null;
    }

    public function getIdFournisseur(): int       { return $this->id_fournisseur; }
    public function getNom(): string              { return $this->nom; }
    public function getContactNom(): ?string      { return $this->contact_nom; }
    public function getContactEmail(): ?string    { return $this->contact_email; }
    public function getContactTelephone(): ?string { return $this->contact_telephone; }

    public function setNom(string $nom): void                       { $this->nom = $nom; }
    public function setContactNom(?string $nom): void               { $this->contact_nom = $nom; }
    public function setContactEmail(?string $email): void           { $this->contact_email = $email; }
    public function setContactTelephone(?string $tel): void         { $this->contact_telephone = $tel; }

    public function toArray(): array {
        return [
            'id_fournisseur'    => $this->id_fournisseur,
            'nom'               => $this->nom,
            'contact_nom'       => $this->contact_nom,
            'contact_email'     => $this->contact_email,
            'contact_telephone' => $this->contact_telephone,
        ];
    }
}
