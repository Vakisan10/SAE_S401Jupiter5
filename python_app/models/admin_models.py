from config.database import get_db

class AdminModels:

    def count_utilisateurs(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM utilisateur")
            return c.fetchone()["n"]

    def count_devis(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM devis")
            return c.fetchone()["n"]

    def count_bons_commande(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM bon_commande")
            return c.fetchone()["n"]

    def count_colis(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM colis")
            return c.fetchone()["n"]

    def count_utilisateurs_par_role(self):
        with get_db().cursor() as c:
            c.execute("""SELECT r.libelle, COUNT(u.id_utilisateur) AS total
                         FROM role r LEFT JOIN utilisateur u ON u.role_id=r.id_role
                         GROUP BY r.libelle""")
            return c.fetchall()

    def get_tous_les_utilisateurs(self):
        with get_db().cursor() as c:
            c.execute("""SELECT u.id_utilisateur, u.uid_cas, u.fullName, u.email,
                                r.libelle AS role, u.role_id, d.nom AS departement, u.departement_id
                         FROM utilisateur u
                         JOIN role r ON u.role_id=r.id_role
                         LEFT JOIN departement d ON u.departement_id=d.id_departement
                         ORDER BY u.fullName""")
            return c.fetchall()

    def get_roles(self):
        with get_db().cursor() as c:
            c.execute("SELECT id_role, libelle FROM role")
            return c.fetchall()

    def get_departements(self):
        with get_db().cursor() as c:
            c.execute("SELECT id_departement, nom FROM departement ORDER BY nom")
            return c.fetchall()

    def update_utilisateur(self, id, role_id, departement_id):
        with get_db().cursor() as c:
            c.execute("UPDATE utilisateur SET role_id=%s, departement_id=%s WHERE id_utilisateur=%s",
                      (role_id, departement_id, id))

    def get_fournisseurs(self):
        with get_db().cursor() as c:
            c.execute("SELECT * FROM fournisseur ORDER BY nom")
            return c.fetchall()

    def ajouter_fournisseur(self, data):
        with get_db().cursor() as c:
            c.execute("INSERT INTO fournisseur (nom,contact_nom,contact_email,contact_telephone) VALUES (%s,%s,%s,%s)",
                      (data["nom"], data["contact_nom"], data["contact_email"], data["contact_telephone"]))

    def update_fournisseur(self, id, data):
        with get_db().cursor() as c:
            c.execute("UPDATE fournisseur SET nom=%s,contact_nom=%s,contact_email=%s,contact_telephone=%s WHERE id_fournisseur=%s",
                      (data["nom"], data["contact_nom"], data["contact_email"], data["contact_telephone"], id))

    def get_fournisseur_by_id(self, id):
        with get_db().cursor() as c:
            c.execute("SELECT * FROM fournisseur WHERE id_fournisseur=%s", (id,))
            return c.fetchone()

    def get_departements_admin(self):
        with get_db().cursor() as c:
            c.execute("SELECT id_departement, nom, budget_total, budget_utilise FROM departement ORDER BY nom")
            return c.fetchall()

    def ajouter_departement(self, nom, budget):
        with get_db().cursor() as c:
            c.execute("INSERT INTO departement (nom,budget_total,budget_utilise) VALUES (%s,%s,0)", (nom, budget))

    def get_departement_by_id(self, id):
        with get_db().cursor() as c:
            c.execute("SELECT * FROM departement WHERE id_departement=%s", (id,))
            return c.fetchone()

    def update_departement(self, id, nom, budget):
        with get_db().cursor() as c:
            c.execute("UPDATE departement SET nom=%s, budget_total=%s WHERE id_departement=%s", (nom, budget, id))

    def get_tous_les_devis(self, search=None):
        sql = """SELECT d.id_devis, d.objet, d.montant_estime, d.statut, d.date_demande,
                        dep.nom AS departement, f.nom AS fournisseur
                 FROM devis d
                 LEFT JOIN utilisateur u ON d.createur_id=u.id_utilisateur
                 LEFT JOIN departement dep ON u.departement_id=dep.id_departement
                 LEFT JOIN fournisseur f ON d.fournisseur_id=f.id_fournisseur"""
        params = []
        if search:
            sql += " WHERE d.objet LIKE %s OR d.statut LIKE %s OR dep.nom LIKE %s OR f.nom LIKE %s"
            params = [f"%{search}%"] * 4
        sql += " ORDER BY d.date_demande DESC"
        with get_db().cursor() as c:
            c.execute(sql, params)
            return c.fetchall()

    def get_toutes_les_commandes(self, search=None):
        sql = """SELECT b.id_bon_commande, b.numero_commande, b.date_commande, b.montant_estime, b.statut,
                        d.nom AS departement, f.nom AS fournisseur
                 FROM bon_commande b
                 LEFT JOIN departement d ON b.departement_id=d.id_departement
                 LEFT JOIN fournisseur f ON b.fournisseur_id=f.id_fournisseur"""
        params = []
        if search:
            sql += " WHERE b.numero_commande LIKE %s OR b.statut LIKE %s OR d.nom LIKE %s OR f.nom LIKE %s"
            params = [f"%{search}%"] * 4
        sql += " ORDER BY b.date_commande DESC"
        with get_db().cursor() as c:
            c.execute(sql, params)
            return c.fetchall()

    def get_tous_les_colis_admin(self, search=None):
        sql = """SELECT c.id_colis, c.numero_suivi, c.date_reception, c.date_retrait,
                        b.numero_commande, d.nom AS departement, s.libelle AS statut
                 FROM colis c
                 LEFT JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                 LEFT JOIN departement d ON b.departement_id=d.id_departement
                 JOIN statut_colis s ON c.statut_id=s.id_statut"""
        params = []
        if search:
            sql += " WHERE c.numero_suivi LIKE %s OR b.numero_commande LIKE %s OR d.nom LIKE %s OR s.libelle LIKE %s"
            params = [f"%{search}%"] * 4
        sql += " ORDER BY c.date_reception DESC"
        with get_db().cursor() as c:
            c.execute(sql, params)
            return c.fetchall()

    def supprimer_utilisateur(self, id):
        with get_db().cursor() as c:
            c.execute("DELETE FROM utilisateur WHERE id_utilisateur=%s", (id,))

    def supprimer_fournisseur(self, id):
        with get_db().cursor() as c:
            c.execute("DELETE FROM fournisseur WHERE id_fournisseur=%s", (id,))

    def supprimer_departement(self, id):
        with get_db().cursor() as c:
            c.execute("DELETE FROM departement WHERE id_departement=%s", (id,))
