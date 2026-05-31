from config.database import get_db

class PostalUnivModels:

    def get_colis_recus(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM colis"); return c.fetchone()["n"]

    def get_colis_a_transferer(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM colis WHERE statut_id=1"); return c.fetchone()["n"]

    def get_colis_transferes(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM colis WHERE statut_id=2"); return c.fetchone()["n"]

    def get_colis_non_identifies(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM colis WHERE statut_id=4"); return c.fetchone()["n"]

    def get_derniers_colis(self):
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, c.date_reception, s.libelle AS statut
                         FROM colis c JOIN statut_colis s ON c.statut_id=s.id_statut
                         ORDER BY c.date_reception DESC LIMIT 10""")
            return c.fetchall()

    def ajouter_colis_universite(self, data):
        with get_db().cursor() as c:
            c.execute("SELECT id_bon_commande FROM bon_commande WHERE numero_commande=%s", (data["numero_commande"],))
            bc = c.fetchone()
            if not bc:
                c.execute("INSERT INTO colis (bon_commande_id,numero_suivi,date_reception,statut_id,commentaire) VALUES (NULL,%s,NOW(),3,%s)",
                          (data["numero_suivi"], data.get("commentaire")))
            else:
                c.execute("INSERT INTO colis (bon_commande_id,numero_suivi,date_reception,statut_id,commentaire) VALUES (%s,%s,NOW(),1,%s)",
                          (bc["id_bon_commande"], data["numero_suivi"], data.get("commentaire")))

    def get_tous_les_colis(self):
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, c.statut_id, b.numero_commande,
                                d.nom AS departement, s.libelle AS statut, c.date_reception
                         FROM colis c
                         LEFT JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         LEFT JOIN departement d ON b.departement_id=d.id_departement
                         JOIN statut_colis s ON c.statut_id=s.id_statut
                         ORDER BY c.date_reception DESC""")
            return c.fetchall()

    def transferer_vers_iut(self, id):
        with get_db().cursor() as c:
            c.execute("UPDATE colis SET statut_id=2 WHERE id_colis=%s", (id,))

    def get_colis_non_identifies_liste(self):
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, c.date_reception, s.libelle AS statut
                         FROM colis c JOIN statut_colis s ON c.statut_id=s.id_statut
                         WHERE c.statut_id=3 ORDER BY c.date_reception DESC""")
            return c.fetchall()

    def get_historique(self):
        with get_db().cursor() as c:
            c.execute("""SELECT h.date_action, c.id_colis, c.numero_suivi, b.numero_commande,
                                d.nom AS departement, s.libelle AS statut, h.action
                         FROM historique_colis h
                         JOIN colis c ON h.id_colis=c.id_colis
                         LEFT JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         LEFT JOIN departement d ON b.departement_id=d.id_departement
                         LEFT JOIN statut_colis s ON c.statut_id=s.id_statut
                         ORDER BY h.date_action DESC LIMIT 200""")
            return c.fetchall()


class DepartementModels:

    def get_departement_nom(self, dep_id):
        with get_db().cursor() as c:
            c.execute("SELECT nom FROM departement WHERE id_departement=%s", (dep_id,))
            row = c.fetchone()
            return row["nom"] if row else None

    def count_colis_en_attente(self, dep_id):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM colis c JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande WHERE b.departement_id=%s AND c.statut_id=1", (dep_id,))
            return c.fetchone()["n"]

    def count_colis_retires(self, dep_id):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM colis c JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande WHERE b.departement_id=%s AND c.statut_id=3", (dep_id,))
            return c.fetchone()["n"]

    def get_budget_departement(self, dep_id):
        with get_db().cursor() as c:
            c.execute("SELECT budget_total, budget_utilise FROM departement WHERE id_departement=%s", (dep_id,))
            return c.fetchone()

    def get_derniers_colis(self, dep_id):
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, b.numero_commande, c.date_reception, s.libelle AS statut_libelle
                         FROM colis c JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         JOIN statut_colis s ON c.statut_id=s.id_statut
                         WHERE b.departement_id=%s ORDER BY c.date_reception DESC LIMIT 10""", (dep_id,))
            return c.fetchall()

    def get_fournisseurs(self):
        with get_db().cursor() as c:
            c.execute("SELECT id_fournisseur, nom FROM fournisseur ORDER BY nom"); return c.fetchall()

    def insert_devis(self, objet, montant, fournisseur_id, createur_id):
        with get_db().cursor() as c:
            c.execute("INSERT INTO devis (date_demande,objet,montant_estime,statut,fournisseur_id,createur_id) VALUES (CURDATE(),%s,%s,'en_attente',%s,%s)",
                      (objet, montant, fournisseur_id, createur_id))

    def get_mes_devis(self, user_id):
        with get_db().cursor() as c:
            c.execute("""SELECT d.id_devis, d.objet, d.montant_estime, d.date_demande, d.statut, f.nom AS fournisseur_nom
                         FROM devis d JOIN fournisseur f ON d.fournisseur_id=f.id_fournisseur
                         WHERE d.createur_id=%s ORDER BY d.id_devis DESC""", (user_id,))
            return c.fetchall()

    def get_mes_bons_commande(self, dep_id):
        with get_db().cursor() as c:
            c.execute("""SELECT b.id_bon_commande, b.numero_commande, b.date_commande, b.montant_estime, b.statut, f.nom AS fournisseur_nom
                         FROM bon_commande b JOIN fournisseur f ON b.fournisseur_id=f.id_fournisseur
                         WHERE b.departement_id=%s ORDER BY b.id_bon_commande DESC""", (dep_id,))
            return c.fetchall()

    def get_colis_departement(self, dep_id):
        with get_db().cursor() as c:
            c.execute("""SELECT c.id_colis, c.numero_suivi, c.date_reception, c.date_retrait,
                                s.libelle AS statut, b.numero_commande
                         FROM colis c JOIN bon_commande b ON c.bon_commande_id=b.id_bon_commande
                         JOIN statut_colis s ON c.statut_id=s.id_statut
                         WHERE b.departement_id=%s ORDER BY c.date_reception DESC""", (dep_id,))
            return c.fetchall()

    def get_depenses_departement(self, dep_id):
        with get_db().cursor() as c:
            c.execute("SELECT numero_commande, date_commande, montant_estime, statut FROM bon_commande WHERE departement_id=%s ORDER BY date_commande DESC", (dep_id,))
            return c.fetchall()

    def get_fournisseurs_autorises(self):
        with get_db().cursor() as c:
            c.execute("SELECT id_fournisseur, nom, contact_nom, contact_email, contact_telephone FROM fournisseur ORDER BY nom")
            return c.fetchall()


class FinanceModels:

    def count_devis_en_attente(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM devis WHERE statut='en_attente'"); return c.fetchone()["n"]

    def count_bon_commande(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM bon_commande"); return c.fetchone()["n"]

    def get_budgets_departements(self):
        with get_db().cursor() as c:
            c.execute("SELECT nom, budget_total, budget_utilise FROM departement"); return c.fetchall()

    def get_devis_en_attente(self):
        with get_db().cursor() as c:
            c.execute("""SELECT d.id_devis, d.objet, d.montant_estime, dep.nom AS departement
                         FROM devis d JOIN utilisateur u ON d.createur_id=u.id_utilisateur
                         JOIN departement dep ON u.departement_id=dep.id_departement
                         WHERE d.statut='en_attente' ORDER BY d.date_demande DESC""")
            return c.fetchall()

    def get_bons_commande_recents(self):
        with get_db().cursor() as c:
            c.execute("SELECT numero_commande, date_commande, montant_estime, statut FROM bon_commande ORDER BY date_commande DESC LIMIT 10")
            return c.fetchall()

    def valider_devis(self, id):
        with get_db().cursor() as c:
            c.execute("SELECT d.montant_estime, u.departement_id FROM devis d JOIN utilisateur u ON d.createur_id=u.id_utilisateur WHERE d.id_devis=%s", (id,))
            devis = c.fetchone()
            if not devis: return
            c.execute("UPDATE devis SET statut='valide_finance' WHERE id_devis=%s", (id,))
            c.execute("UPDATE departement SET budget_utilise=budget_utilise+%s WHERE id_departement=%s",
                      (devis["montant_estime"], devis["departement_id"]))

    def rejeter_devis(self, id):
        with get_db().cursor() as c:
            c.execute("UPDATE devis SET statut='rejete_finance' WHERE id_devis=%s", (id,))

    def get_devis_a_verifier(self):
        with get_db().cursor() as c:
            c.execute("""SELECT d.id_devis, d.objet, d.montant_estime, d.date_demande, dep.nom AS departement
                         FROM devis d LEFT JOIN utilisateur u ON d.createur_id=u.id_utilisateur
                         LEFT JOIN departement dep ON u.departement_id=dep.id_departement
                         WHERE d.statut='en_attente' ORDER BY d.date_demande DESC""")
            return c.fetchall()

    def get_tous_les_bons_commande(self):
        with get_db().cursor() as c:
            c.execute("""SELECT b.id_bon_commande, b.numero_commande, b.date_commande, b.montant_estime, b.statut,
                                dep.nom AS departement, f.nom AS fournisseur
                         FROM bon_commande b LEFT JOIN departement dep ON b.departement_id=dep.id_departement
                         LEFT JOIN fournisseur f ON b.fournisseur_id=f.id_fournisseur
                         ORDER BY b.date_commande DESC""")
            return c.fetchall()

    def get_budget_departements(self):
        with get_db().cursor() as c:
            c.execute("SELECT nom, budget_total, budget_utilise, (budget_total-budget_utilise) AS budget_restant FROM departement ORDER BY nom")
            return c.fetchall()

    def get_devis_complet(self, id):
        with get_db().cursor() as c:
            c.execute("""SELECT d.id_devis, d.date_demande, d.objet, d.montant_estime, d.statut,
                                f.nom AS fournisseur_nom, f.contact_nom AS fournisseur_contact,
                                f.contact_email AS fournisseur_email, f.contact_telephone AS fournisseur_telephone,
                                u.fullName AS demandeur_nom, u.email AS demandeur_email,
                                dep.nom AS departement_nom, dep.budget_total, dep.budget_utilise
                         FROM devis d LEFT JOIN fournisseur f ON d.fournisseur_id=f.id_fournisseur
                         LEFT JOIN utilisateur u ON d.createur_id=u.id_utilisateur
                         LEFT JOIN departement dep ON u.departement_id=dep.id_departement
                         WHERE d.id_devis=%s""", (id,))
            return c.fetchone()


class DirecteurModels:

    def count_devis_en_attente(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM devis WHERE statut='valide_finance'"); return c.fetchone()["n"]

    def count_bon_commande(self):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM bon_commande"); return c.fetchone()["n"]

    def get_devis_a_valider(self):
        with get_db().cursor() as c:
            c.execute("SELECT id_devis, objet, montant_estime, date_demande FROM devis WHERE statut='valide_finance' ORDER BY date_demande DESC")
            return c.fetchall()

    def get_bon_commande_signes(self):
        with get_db().cursor() as c:
            c.execute("SELECT id_bon_commande, numero_commande, date_commande FROM bon_commande ORDER BY date_commande DESC LIMIT 20")
            return c.fetchall()

    def get_devis_by_id(self, id):
        with get_db().cursor() as c:
            c.execute("SELECT * FROM devis WHERE id_devis=%s", (id,)); return c.fetchone()

    def bon_commande_existe_pour_devis(self, id_devis):
        with get_db().cursor() as c:
            c.execute("SELECT COUNT(*) as n FROM bon_commande WHERE devis_id=%s", (id_devis,))
            return c.fetchone()["n"] > 0

    def signer_devis(self, id_devis):
        import datetime
        num_bc = f"BC-{datetime.date.today().year}-{str(id_devis).zfill(3)}"
        with get_db().cursor() as c:
            c.execute("UPDATE devis SET statut='signe_directeur' WHERE id_devis=%s", (id_devis,))
            c.execute("""INSERT INTO bon_commande (numero_commande, date_commande, montant_estime, fournisseur_id, createur_id, departement_id, devis_id)
                         SELECT %s, CURDATE(), d.montant_estime, d.fournisseur_id, d.createur_id, u.departement_id, d.id_devis
                         FROM devis d JOIN utilisateur u ON d.createur_id=u.id_utilisateur
                         WHERE d.id_devis=%s""", (num_bc, id_devis))

    def get_tous_les_bons_commande(self):
        with get_db().cursor() as c:
            c.execute("""SELECT b.id_bon_commande, b.numero_commande, b.date_commande, d.objet, d.montant_estime
                         FROM bon_commande b LEFT JOIN devis d ON b.devis_id=d.id_devis
                         ORDER BY b.date_commande DESC""")
            return c.fetchall()

    def get_devis_complet(self, id):
        with get_db().cursor() as c:
            c.execute("""SELECT d.id_devis, d.date_demande, d.objet, d.montant_estime, d.statut,
                                f.nom AS fournisseur_nom, f.contact_email AS fournisseur_email,
                                u.fullName AS demandeur_nom, dep.nom AS departement_nom
                         FROM devis d LEFT JOIN fournisseur f ON d.fournisseur_id=f.id_fournisseur
                         LEFT JOIN utilisateur u ON d.createur_id=u.id_utilisateur
                         LEFT JOIN departement dep ON u.departement_id=dep.id_departement
                         WHERE d.id_devis=%s""", (id,))
            return c.fetchone()
