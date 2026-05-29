<?php
// models/Utilisateur.php

class Utilisateur {

    private int     $id_utilisateur;
    private string  $uid_cas;
    private string  $access_token_api_cas;
    private string  $fullName;
    private string  $email;
    private int     $role_id;
    private ?int    $departement_id;

    public function __construct(array $dico) {
        $this->id_utilisateur       = $dico['id_utilisateur']       ?? 0;
        $this->uid_cas              = $dico['uid_cas'];
        $this->access_token_api_cas = $dico['access_token_api_cas'] ?? '';
        $this->fullName             = $dico['fullName'];
        $this->email                = $dico['email'];
        $this->role_id              = $dico['role_id'];
        $this->departement_id       = $dico['departement_id']       ?? null;
    }

    public function getIdUtilisateur(): int       { return $this->id_utilisateur; }
    public function getUidCas(): string           { return $this->uid_cas; }
    public function getAccessToken(): string      { return $this->access_token_api_cas; }
    public function getFullName(): string         { return $this->fullName; }
    public function getEmail(): string            { return $this->email; }
    public function getRoleId(): int              { return $this->role_id; }
    public function getDepartementId(): ?int      { return $this->departement_id; }

    public function setRoleId(int $role_id): void            { $this->role_id = $role_id; }
    public function setDepartementId(?int $id): void         { $this->departement_id = $id; }

    public function toArray(): array {
        return [
            'id_utilisateur'       => $this->id_utilisateur,
            'uid_cas'              => $this->uid_cas,
            'access_token_api_cas' => $this->access_token_api_cas,
            'fullName'             => $this->fullName,
            'email'                => $this->email,
            'role_id'              => $this->role_id,
            'departement_id'       => $this->departement_id,
        ];
    }
}
