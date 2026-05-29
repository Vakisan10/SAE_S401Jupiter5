<?php
// models/Devis.php

class Devis {

    private int     $id_devis;
    private string  $date_demande;
    private ?string $objet;
    private ?float  $montant_estime;
    private ?string $fichier_pdf;
    private string  $statut;
    private int     $fournisseur_id;
    private int     $createur_id;

    public function __construct(array $dico) {
        $this->id_devis       = $dico['id_devis']       ?? 0;
        $this->date_demande   = $dico['date_demande']   ?? date('Y-m-d');
        $this->objet          = $dico['objet']          ?? null;
        $this->montant_estime = isset($dico['montant_estime']) ? (float) $dico['montant_estime'] : null;
        $this->fichier_pdf    = $dico['fichier_pdf']    ?? null;
        $this->statut         = $dico['statut']         ?? 'en_attente';
        $this->fournisseur_id = $dico['fournisseur_id'];
        $this->createur_id    = $dico['createur_id'];
    }

    public function getIdDevis(): int           { return $this->id_devis; }
    public function getDateDemande(): string    { return $this->date_demande; }
    public function getObjet(): ?string         { return $this->objet; }
    public function getMontantEstime(): ?float  { return $this->montant_estime; }
    public function getFichierPdf(): ?string    { return $this->fichier_pdf; }
    public function getStatut(): string         { return $this->statut; }
    public function getFournisseurId(): int     { return $this->fournisseur_id; }
    public function getCreateurId(): int        { return $this->createur_id; }

    public function setStatut(string $statut): void          { $this->statut = $statut; }
    public function setFichierPdf(?string $pdf): void        { $this->fichier_pdf = $pdf; }

    public function toArray(): array {
        return [
            'id_devis'        => $this->id_devis,
            'date_demande'    => $this->date_demande,
            'objet'           => $this->objet,
            'montant_estime'  => $this->montant_estime,
            'fichier_pdf'     => $this->fichier_pdf,
            'statut'          => $this->statut,
            'fournisseur_id'  => $this->fournisseur_id,
            'createur_id'     => $this->createur_id,
        ];
    }
}
