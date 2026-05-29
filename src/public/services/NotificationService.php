<?php
require_once __DIR__ . '/../models/Model.php';

class NotificationService {

    private $db;

    public function __construct() {
        $this->db = Model::getModel()->bd;
    }

    /**
     * Crée une notification pour un utilisateur
     */
    public function notifier(int $utilisateur_id, string $message, string $type = 'info') {
        $req = $this->db->prepare("
            INSERT INTO notification (utilisateur_id, message, type, date_creation, lu)
            VALUES (?, ?, ?, NOW(), 0)
        ");
        return $req->execute([$utilisateur_id, $message, $type]);
    }

    /**
     * Notifie le département qu'un colis est arrivé
     */
    public function notifierArriveColis(int $bon_commande_id) {
        $req = $this->db->prepare("
            SELECT u.id_utilisateur
            FROM utilisateur u
            JOIN bon_commande b ON u.departement_id = b.departement_id
            WHERE b.id_bon_commande = ?
        ");
        $req->execute([$bon_commande_id]);
        $utilisateurs = $req->fetchAll(PDO::FETCH_ASSOC);

        foreach ($utilisateurs as $u) {
            $this->notifier(
                $u['id_utilisateur'],
                "Un colis associé à votre bon de commande est arrivé.",
                'colis'
            );
        }
    }

    /**
     * Notifie le département que son devis a été validé ou refusé
     */
    public function notifierStatutDevis(int $devis_id, string $statut) {
        $req = $this->db->prepare("
            SELECT u.id_utilisateur
            FROM utilisateur u
            JOIN devis d ON u.departement_id = d.departement_id
            WHERE d.id_devis = ?
        ");
        $req->execute([$devis_id]);
        $utilisateurs = $req->fetchAll(PDO::FETCH_ASSOC);

        $message = $statut === 'valide'
            ? "Votre devis a été validé."
            : "Votre devis a été refusé.";

        foreach ($utilisateurs as $u) {
            $this->notifier($u['id_utilisateur'], $message, $statut);
        }
    }

    /**
     * Récupère les notifications non lues d'un utilisateur
     */
    public function getNotificationsNonLues(int $utilisateur_id): array {
        $req = $this->db->prepare("
            SELECT * FROM notification
            WHERE utilisateur_id = ? AND lu = 0
            ORDER BY date_creation DESC
        ");
        $req->execute([$utilisateur_id]);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public function marquerToutesLues(int $utilisateur_id) {
        $req = $this->db->prepare("
            UPDATE notification SET lu = 1
            WHERE utilisateur_id = ?
        ");
        return $req->execute([$utilisateur_id]);
    }
}
