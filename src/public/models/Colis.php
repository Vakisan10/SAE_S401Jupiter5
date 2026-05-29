<?php
// models/Colis.php

class Colis {

    private int     $id_colis;
    private int     $bon_commande_id;
    private int     $statut_id;
    private ?string $numero_suivi;
    private ?string $code_barres;
    private ?int    $destinataire_id;
    private ?string $date_reception;
    private ?string $date_retrait;
    private ?string $commentaire;
    private ?int    $receptionne_par;

    public function __construct(array $dico) {
        $this->id_colis        = $dico['id_colis']        ?? 0;
        $this->bon_commande_id = $dico['bon_commande_id'];
        $this->statut_id       = $dico['statut_id']       ?? 1;
        $this->numero_suivi    = $dico['numero_suivi']    ?? null;
        $this->code_barres     = $dico['code_barres']     ?? null;
        $this->destinataire_id = $dico['destinataire_id'] ?? null;
        $this->date_reception  = $dico['date_reception']  ?? null;
        $this->date_retrait    = $dico['date_retrait']    ?? null;
        $this->commentaire     = $dico['commentaire']     ?? null;
        $this->receptionne_par = $dico['receptionne_par'] ?? null;
    }

    // Getters
    public function getIdColis(): int            { return $this->id_colis; }
    public function getBonCommandeId(): int      { return $this->bon_commande_id; }
    public function getStatutId(): int           { return $this->statut_id; }
    public function getNumeroSuivi(): ?string    { return $this->numero_suivi; }
    public function getCodeBarres(): ?string     { return $this->code_barres; }
    public function getDestinataire(): ?int      { return $this->destinataire_id; }
    public function getDateReception(): ?string  { return $this->date_reception; }
    public function getDateRetrait(): ?string    { return $this->date_retrait; }
    public function getCommentaire(): ?string    { return $this->commentaire; }
    public function getReceptionnepar(): ?int    { return $this->receptionne_par; }

    // Setters
    public function setStatutId(int $statut_id): void       { $this->statut_id = $statut_id; }
    public function setDestinataire(?int $id): void         { $this->destinataire_id = $id; }
    public function setDateRetrait(?string $date): void     { $this->date_retrait = $date; }
    public function setCommentaire(?string $comment): void  { $this->commentaire = $comment; }
    public function setReceptionnepar(?int $id): void       { $this->receptionne_par = $id; }

    public function toArray(): array {
        return [
            'id_colis'        => $this->id_colis,
            'bon_commande_id' => $this->bon_commande_id,
            'statut_id'       => $this->statut_id,
            'numero_suivi'    => $this->numero_suivi,
            'code_barres'     => $this->code_barres,
            'destinataire_id' => $this->destinataire_id,
            'date_reception'  => $this->date_reception,
            'date_retrait'    => $this->date_retrait,
            'commentaire'     => $this->commentaire,
            'receptionne_par' => $this->receptionne_par,
        ];
    }
}
