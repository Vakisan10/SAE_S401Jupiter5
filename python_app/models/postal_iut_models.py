from config.database import get_db

class PostalIutModels:

    def get_colis_recus_iut(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM colis WHERE statut_id=2"); return c.fetchone()["n"]

    def get_colis_en_attente(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM colis WHERE statut_id=3"); return c.fetchone()["n"]

    def get_colis_retires(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM colis WHERE statut_id=4"); return c.fetchone()["n"]

    def get_colis_non_identifies(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM colis WHERE statut_id=1 AND destinataire_id IS NULL")
            return c.fetchone()["n"]

    def get_derniers_colis(self):
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, c.date_reception, s.libelle AS statut, d.nom AS departement
                         FROM colis c
                         LEFT JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         LEFT JOIN departement d ON b.departement_id=d.id_departement
                         JOIN statut_colis s ON c.statut_id=s.id_statut
                         ORDER BY c.date_reception DESC LIMIT 10""")
            return c.fetchall()

    def get_colis_recus(self):
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, c.date_reception, d.nom AS departement, s.libelle AS statut
                         FROM colis c
                         LEFT JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         LEFT JOIN departement d ON b.departement_id=d.id_departement
                         JOIN statut_colis s ON c.statut_id=s.id_statut
                         WHERE c.statut_id=2 ORDER BY c.date_reception DESC""")
            return c.fetchall()

    def get_colis_remis(self):
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, c.date_reception, c.date_retrait, d.nom AS departement
                         FROM colis c
                         LEFT JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         LEFT JOIN departement d ON b.departement_id=d.id_departement
                         WHERE c.statut_id=4 ORDER BY c.date_retrait DESC""")
            return c.fetchall()

    def get_liste_colis_en_attente(self):
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, c.date_reception, b.numero_commande,
                                d.nom AS departement, s.libelle AS statut
                         FROM colis c
                         LEFT JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         LEFT JOIN departement d ON b.departement_id=d.id_departement
                         JOIN statut_colis s ON c.statut_id=s.id_statut
                         WHERE c.statut_id=3 ORDER BY c.date_reception DESC""")
            return c.fetchall()

    def get_colis_non_identifie(self):
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, c.date_reception, c.commentaire
                         FROM colis c WHERE c.statut_id=1 AND c.destinataire_id IS NULL
                         ORDER BY c.date_reception DESC""")
            return c.fetchall()

    def get_colis_a_confirmer(self):
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, c.date_reception, d.nom AS departement, b.numero_commande
                         FROM colis c
                         LEFT JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         LEFT JOIN departement d ON b.departement_id=d.id_departement
                         WHERE c.statut_id=1 ORDER BY c.date_reception DESC""")
            return c.fetchall()

    def get_colis_by_id(self, id):
        with get_db().cursor() as c:
            c.execute("""SELECT c.*, s.libelle AS statut, b.numero_commande, d.nom AS departement
                         FROM colis c
                         LEFT JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         LEFT JOIN departement d ON b.departement_id=d.id_departement
                         JOIN statut_colis s ON c.statut_id=s.id_statut
                         WHERE c.id_colis=%s""", (id,))
            return c.fetchone()

    def get_historique_colis(self, id):
        with get_db().cursor() as c:
            c.execute("SELECT action, date_action FROM historique_colis WHERE id_colis=%s ORDER BY date_action DESC", (id,))
            return c.fetchall()

    def get_historique_global(self):
        with get_db().cursor() as c:
            c.execute("""SELECT h.id_colis, h.date_action, h.action, c.numero_suivi, b.numero_commande
                         FROM historique_colis h
                         LEFT JOIN colis c ON h.id_colis=c.id_colis
                         LEFT JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         ORDER BY h.date_action DESC LIMIT 200""")
            return c.fetchall()

    def rechercher_colis(self, motcle):
        s = f"%{motcle}%"
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, c.date_reception, s.libelle AS statut,
                                d.nom AS departement, b.numero_commande
                         FROM colis c
                         LEFT JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         LEFT JOIN departement d ON b.departement_id=d.id_departement
                         JOIN statut_colis s ON c.statut_id=s.id_statut
                         WHERE c.numero_suivi LIKE %s OR b.numero_commande LIKE %s OR d.nom LIKE %s
                         ORDER BY c.date_reception DESC""", (s, s, s))
            return c.fetchall()

    def get_bc_info(self, num_bc):
        with get_db().cursor() as c:
            c.execute("""SELECT b.id_bon_commande, u.id_utilisateur AS destinataire_id, d.id_departement
                         FROM bon_commande b
                         LEFT JOIN utilisateur u ON b.createur_id=u.id_utilisateur
                         LEFT JOIN departement d ON b.departement_id=d.id_departement
                         WHERE b.numero_commande=%s""", (num_bc,))
            return c.fetchone()

    def insert_colis(self, data):
        with get_db().cursor() as c:
            c.execute("""INSERT INTO colis (bon_commande_id, numero_suivi, destinataire_id, date_reception, statut_id, commentaire)
                         VALUES (%s, %s, %s, NOW(), %s, %s)""",
                      (data.get("bon_commande_id"), data.get("numero_suivi"), data.get("destinataire_id"),
                       data.get("statut_id", 1), data.get("commentaire")))
            self.add_historique(get_db().insert_id(), "Colis créé")
            return True

    def update_colis(self, id, data):
        with get_db().cursor() as c:
            c.execute("""UPDATE colis SET numero_suivi=%s, bon_commande_id=%s, destinataire_id=%s, statut_id=%s, commentaire=%s
                         WHERE id_colis=%s""",
                      (data["numero_suivi"], data["bon_commande_id"], data["destinataire_id"],
                       data["statut_id"], data["commentaire"], id))
            self.add_historique(id, "Colis modifié")

    def confirmer_reception_iut(self, id):
        with get_db().cursor() as c:
            c.execute("UPDATE colis SET statut_id=2 WHERE id_colis=%s", (id,))
            self.add_historique(id, "Réception confirmée à l'IUT")

    def marquer_colis_retire(self, id):
        with get_db().cursor() as c:
            c.execute("UPDATE colis SET statut_id=4, date_retrait=NOW() WHERE id_colis=%s", (id,))
            self.add_historique(id, "Colis retiré")

    def add_historique(self, colis_id, action):
        with get_db().cursor() as c:
            c.execute("INSERT INTO historique_colis (id_colis, action, date_action) VALUES (%s, %s, NOW())", (colis_id, action))

    def get_all_statuts(self):
        with get_db().cursor() as c:
            c.execute("SELECT * FROM statut_colis"); return c.fetchall()

    def get_all_departements(self):
        with get_db().cursor() as c:
            c.execute("SELECT * FROM departement ORDER BY nom"); return c.fetchall()

    def get_bon_commandes(self):
        with get_db().cursor() as c:
            c.execute("SELECT id_bon_commande, numero_commande FROM bon_commande ORDER BY date_commande DESC")
            return c.fetchall()
