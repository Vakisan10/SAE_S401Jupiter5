from config.database import get_db


class NotificationService:
    """Service de gestion des notifications utilisateurs."""

    def notifier(self, utilisateur_id: int, message: str, type_notif: str = 'info') -> bool:
        """Crée une notification pour un utilisateur."""
        try:
            with get_db().cursor() as c:
                c.execute(
                    """INSERT INTO notification (utilisateur_id, message, type, date_creation, lu)
                       VALUES (%s, %s, %s, NOW(), 0)""",
                    (utilisateur_id, message, type_notif)
                )
            return True
        except Exception:
            return False

    def notifier_arrive_colis(self, bon_commande_id: int):
        """Notifie le département qu'un colis associé à son bon de commande est arrivé."""
        with get_db().cursor() as c:
            c.execute(
                """SELECT u.id_utilisateur
                   FROM utilisateur u
                   JOIN bon_commande b ON u.departement_id = b.departement_id
                   WHERE b.id_bon_commande = %s""",
                (bon_commande_id,)
            )
            utilisateurs = c.fetchall()

        for u in utilisateurs:
            self.notifier(
                u['id_utilisateur'],
                "Un colis associé à votre bon de commande est arrivé.",
                'colis'
            )

    def notifier_statut_devis(self, devis_id: int, statut: str):
        """Notifie le département que son devis a été validé ou refusé."""
        with get_db().cursor() as c:
            c.execute(
                """SELECT u.id_utilisateur
                   FROM utilisateur u
                   JOIN devis d ON u.departement_id = d.departement_id
                   WHERE d.id_devis = %s""",
                (devis_id,)
            )
            utilisateurs = c.fetchall()

        message = "Votre devis a été validé." if statut == 'valide' else "Votre devis a été refusé."

        for u in utilisateurs:
            self.notifier(u['id_utilisateur'], message, statut)

    def get_notifications_non_lues(self, utilisateur_id: int) -> list:
        """Récupère les notifications non lues d'un utilisateur."""
        with get_db().cursor() as c:
            c.execute(
                """SELECT * FROM notification
                   WHERE utilisateur_id = %s AND lu = 0
                   ORDER BY date_creation DESC""",
                (utilisateur_id,)
            )
            return c.fetchall()

    def marquer_toutes_lues(self, utilisateur_id: int) -> bool:
        """Marque toutes les notifications d'un utilisateur comme lues."""
        try:
            with get_db().cursor() as c:
                c.execute(
                    "UPDATE notification SET lu = 1 WHERE utilisateur_id = %s",
                    (utilisateur_id,)
                )
            return True
        except Exception:
            return False
