<?php
// models/BonCommande.php

class BonCommande {

    private int     $id_bon_commande;
    private string  $numero_commande;
    private string  $date_commande;
    private ?string $date_estimee_livraison;
    private float   $montant_estime;
    private string  $statut;
    private int     $departement_id;
    private int     $fournisseur_id;
    private int     $createur_id;
    private int     $devis_id;
    private ?string $commentaire;

    public function __construct(array $dico) {
        $this->id_bon_commande        = $dico['id_bon_commande']        ?? 0;
        $this->numero_commande        = $dico['numero_commande'];
        $this->date_commande          = $dico['date_commande']          ?? date('Y-m-d');
        $this->date_estimee_livraison = $dico['date_estimee_livraison'] ?? null;
        $this->montant_estime         = (float) ($dico['montant_estime'] ?? 0);
        $this->statut                 = $dico['statut']                 ?? 'en_preparation';
        $this->departement_id         = $dico['departement_id'];
        $this->fournisseur_id         = $dico['fournisseur_id'];
        $this->createur_id            = $dico['createur_id'];
        $this->devis_id               = $dico['devis_id'];
        $this->commentaire            = $dico['commentaire']            ?? null;
    }

    public function getIdBonCommande(): int           { return $this->id_bon_commande; }
    public function getNumeroCommande(): string        { return $this->numero_commande; }
    public function getDateCommande(): string          { return $this->date_commande; }
    public function getDateEsteeLivraison(): ?string   { return $this->date_estimee_livraison; }
    public function getMontantEstime(): float          { return $this->montant_estime; }
    public function getStatut(): string               { return $this->statut; }
    public function getDepartementId(): int           { return $this->departement_id; }
    public function getFournisseurId(): int           { return $this->fournisseur_id; }
    public function getCreateurId(): int              { return $this->createur_id; }
    public function getDevisId(): int                 { return $this->devis_id; }
    public function getCommentaire(): ?string         { return $this->commentaire; }

    public function setStatut(string $statut): void              { $this->statut = $statut; }
    public function setCommentaire(?string $comment): void       { $this->commentaire = $comment; }
    public function setDateLivraison(?string $date): void        { $this->date_estimee_livraison = $date; }

    public function toArray(): array {
        return [
            'id_bon_commande'        => $this->id_bon_commande,
            'numero_commande'        => $this->numero_commande,
            'date_commande'          => $this->date_commande,
            'date_estimee_livraison' => $this->date_estimee_livraison,
            'montant_estime'         => $this->montant_estime,
            'statut'                 => $this->statut,
            'departement_id'         => $this->departement_id,
            'fournisseur_id'         => $this->fournisseur_id,
            'createur_id'            => $this->createur_id,
            'devis_id'               => $this->devis_id,
            'commentaire'            => $this->commentaire,
        ];
    }
}
