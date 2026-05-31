from flask import Blueprint, render_template, request, redirect, session, abort
from models.admin_models import AdminModels
from models.other_models import PostalUnivModels, DepartementModels, FinanceModels, DirecteurModels
from models.postal_iut_models import PostalIutModels

# ===== ADMIN =====
admin_bp = Blueprint("admin", __name__, url_prefix="/admin")

@admin_bp.route("/dashboard")
def dashboard():
    m = AdminModels()
    stats = {"utilisateurs": m.count_utilisateurs(), "devis": m.count_devis(),
             "bons": m.count_bons_commande(), "colis": m.count_colis()}
    roles = m.count_utilisateurs_par_role()
    return render_template("admin/dashboard.html", stats=stats, roles=roles)

@admin_bp.route("/utilisateurs")
def utilisateurs():
    m = AdminModels()
    return render_template("admin/utilisateurs.html",
        utilisateurs=m.get_tous_les_utilisateurs(), roles=m.get_roles(), departements=m.get_departements())

@admin_bp.route("/update-utilisateur", methods=["POST"])
def update_utilisateur():
    m = AdminModels()
    m.update_utilisateur(request.form["id_utilisateur"], request.form["role_id"],
                         request.form.get("departement_id") or None)
    return redirect("/admin/utilisateurs?ok=1")

@admin_bp.route("/fournisseurs")
def fournisseurs():
    return render_template("admin/fournisseurs.html", fournisseurs=AdminModels().get_fournisseurs())

@admin_bp.route("/ajouter-fournisseur", methods=["POST"])
def ajouter_fournisseur():
    AdminModels().ajouter_fournisseur(request.form)
    return redirect("/admin/fournisseurs")

@admin_bp.route("/modifier-fournisseur")
def modifier_fournisseur():
    f = AdminModels().get_fournisseur_by_id(request.args.get("id"))
    return render_template("admin/modifier-fournisseur.html", fournisseur=f)

@admin_bp.route("/update-fournisseur", methods=["POST"])
def update_fournisseur():
    AdminModels().update_fournisseur(request.form["id_fournisseur"], request.form)
    return redirect("/admin/fournisseurs")

@admin_bp.route("/supprimer-fournisseur", methods=["POST"])
def supprimer_fournisseur():
    AdminModels().supprimer_fournisseur(request.form["id_fournisseur"])
    return redirect("/admin/fournisseurs?deleted=1")

@admin_bp.route("/departements")
def departements():
    return render_template("admin/departements.html", departements=AdminModels().get_departements_admin())

@admin_bp.route("/ajouter-departement", methods=["POST"])
def ajouter_departement():
    AdminModels().ajouter_departement(request.form["nom"], request.form["budget_total"])
    return redirect("/admin/departements")

@admin_bp.route("/modifier-departement")
def modifier_departement():
    d = AdminModels().get_departement_by_id(request.args.get("id"))
    return render_template("admin/modifier-departement.html", departement=d)

@admin_bp.route("/update-departement", methods=["POST"])
def update_departement():
    AdminModels().update_departement(request.form["id_departement"], request.form["nom"], request.form["budget_total"])
    return redirect("/admin/departements")

@admin_bp.route("/supprimer-departement", methods=["POST"])
def supprimer_departement():
    AdminModels().supprimer_departement(request.form["id_departement"])
    return redirect("/admin/departements?deleted=1")

@admin_bp.route("/devis")
def devis():
    m = AdminModels()
    return render_template("admin/devis.html", devis=m.get_tous_les_devis(request.args.get("q")),
                           stats=m.count_devis_par_statut())

@admin_bp.route("/commandes")
def commandes():
    m = AdminModels()
    return render_template("admin/commandes.html", commandes=m.get_toutes_les_commandes(request.args.get("q")),
                           stats=m.count_commandes_par_statut())

@admin_bp.route("/colis")
def colis():
    m = AdminModels()
    return render_template("admin/colis.html", colis=m.get_tous_les_colis_admin(request.args.get("q")),
                           stats=m.count_colis_par_statut())

@admin_bp.route("/supprimer-utilisateur", methods=["POST"])
def supprimer_utilisateur():
    AdminModels().supprimer_utilisateur(request.form["id_utilisateur"])
    return redirect("/admin/utilisateurs?deleted=1")


# ===== POSTAL IUT =====
postal_iut_bp = Blueprint("postal_iut", __name__, url_prefix="/postal")

@postal_iut_bp.route("/dashboard")
def dashboard():
    m = PostalIutModels()
    stats = {"recus": m.get_colis_recus_iut(), "en_attente": m.get_colis_en_attente(),
             "retires": m.get_colis_retires(), "non_identifies": m.get_colis_non_identifies()}
    return render_template("postal_iut/dashboard.html", stats=stats, colis=m.get_derniers_colis())

@postal_iut_bp.route("/colis/recus")
def colis_recus():
    return render_template("postal_iut/colis-recus.html", colis=PostalIutModels().get_colis_recus())

@postal_iut_bp.route("/colis/remis")
def colis_remis():
    return render_template("postal_iut/colis-remis.html", colis=PostalIutModels().get_colis_remis())

@postal_iut_bp.route("/colis/attente")
def colis_attente():
    return render_template("postal_iut/colis-attente.html", colis=PostalIutModels().get_liste_colis_en_attente())

@postal_iut_bp.route("/colis/non-identifies")
def colis_non_identifies():
    return render_template("postal_iut/colis-non-identifies.html", colis=PostalIutModels().get_colis_non_identifie())

@postal_iut_bp.route("/colis/details")
def colis_details():
    id = request.args.get("id", type=int)
    m = PostalIutModels()
    colis = m.get_colis_by_id(id)
    if not colis: abort(404)
    return render_template("postal_iut/colis-details.html", colis=colis, historique=m.get_historique_colis(id))

@postal_iut_bp.route("/colis/ajouter", methods=["GET", "POST"])
def ajouter_colis():
    message = None
    if request.method == "POST":
        m = PostalIutModels()
        bc_info = m.get_bc_info(request.form["numero_bc"])
        if not bc_info:
            message = "Numéro de bon de commande introuvable."
        else:
            m.insert_colis({"bon_commande_id": bc_info["id_bon_commande"],
                            "numero_suivi": request.form.get("numero_suivi", ""),
                            "destinataire_id": bc_info["destinataire_id"],
                            "statut_id": 1,
                            "commentaire": request.form.get("commentaire", "")})
            message = "Colis ajouté avec succès."
    return render_template("postal_iut/ajouter-colis.html", message=message)

@postal_iut_bp.route("/colis/modifier")
def modifier_colis():
    id = request.args.get("id", type=int)
    m = PostalIutModels()
    colis = m.get_colis_by_id(id)
    if not colis: return redirect("/postal/colis/recus")
    return render_template("postal_iut/modifier-colis.html", colis=colis,
                           statuts=m.get_all_statuts(), departements=m.get_all_departements(),
                           bon_commandes=m.get_bon_commandes())

@postal_iut_bp.route("/colis/update", methods=["POST"])
def update_colis():
    id = request.form.get("id_colis", type=int)
    PostalIutModels().update_colis(id, {
        "numero_suivi": request.form.get("numero_suivi"),
        "bon_commande_id": request.form.get("bon_commande_id") or None,
        "destinataire_id": request.form.get("destinataire_id") or None,
        "statut_id": request.form.get("statut_id", 1),
        "commentaire": request.form.get("commentaire")
    })
    return redirect(f"/postal/colis/details?id={id}")

@postal_iut_bp.route("/colis/retirer")
def retirer_colis():
    id = request.args.get("id", type=int)
    PostalIutModels().marquer_colis_retire(id)
    return redirect("/postal/colis/recus?ok=1")

@postal_iut_bp.route("/colis/recherche")
def recherche_colis():
    q = request.args.get("q", "")
    resultats = PostalIutModels().rechercher_colis(q) if q else []
    return render_template("postal_iut/recherche-colis.html", resultats=resultats)

@postal_iut_bp.route("/confirmation")
def confirmation():
    return render_template("postal_iut/confirmation.html", colis=PostalIutModels().get_colis_a_confirmer())

@postal_iut_bp.route("/confirmer")
def confirmer_colis():
    id = request.args.get("id", type=int)
    PostalIutModels().confirmer_reception_iut(id)
    return redirect("/postal/confirmation?ok=1")

@postal_iut_bp.route("/historique")
def historique():
    return render_template("postal_iut/historique.html", historique=PostalIutModels().get_historique_global())


# ===== POSTAL UNIV =====
postal_univ_bp = Blueprint("postal_univ", __name__, url_prefix="/postal-univ")

@postal_univ_bp.route("/dashboard")
def dashboard():
    m = PostalUnivModels()
    stats = {"recus": m.get_colis_recus(), "a_transferer": m.get_colis_a_transferer(),
             "transferes": m.get_colis_transferes(), "non_identifies": m.get_colis_non_identifies()}
    return render_template("postal_univ/dashboard.html", stats=stats, colis_recents=m.get_derniers_colis())

@postal_univ_bp.route("/reception", methods=["GET", "POST"])
def reception_colis():
    if request.method == "POST":
        PostalUnivModels().ajouter_colis_universite(request.form)
        return redirect("/postal-univ/reception?ok=1")
    return render_template("postal_univ/reception-colis.html")

@postal_univ_bp.route("/colis")
def liste_colis():
    return render_template("postal_univ/colis.html", colis=PostalUnivModels().get_tous_les_colis())

@postal_univ_bp.route("/transferer")
def transferer_colis():
    id = request.args.get("id", type=int)
    PostalUnivModels().transferer_vers_iut(id)
    return redirect("/postal-univ/colis?transfer=ok")

@postal_univ_bp.route("/non-identifies")
def non_identifies():
    return render_template("postal_univ/non-identifies.html", colis=PostalUnivModels().get_colis_non_identifies_liste())

@postal_univ_bp.route("/historique")
def historique():
    return render_template("postal_univ/historique.html", historique=PostalUnivModels().get_historique())


# ===== DEPARTEMENT =====
departement_bp = Blueprint("departement", __name__, url_prefix="/departement")

def _dep_id():
    return session.get("user", {}).get("departement_id", 1)

def _user_id():
    return session.get("user", {}).get("id")

@departement_bp.route("/dashboard")
def dashboard():
    dep_id = _dep_id()
    m = DepartementModels()
    stats = {"en_attente": m.count_colis_en_attente(dep_id), "retire": m.count_colis_retires(dep_id)}
    budget = m.get_budget_departement(dep_id)
    if budget:
        budget["budget_restant"] = budget["budget_total"] - budget["budget_utilise"]
    return render_template("departement/dashboard.html", stats=stats, budget=budget, colis=m.get_derniers_colis(dep_id))

@departement_bp.route("/creer-devis")
def creer_devis():
    return render_template("departement/creer-devis.html", fournisseurs=DepartementModels().get_fournisseurs())

@departement_bp.route("/envoyer-devis", methods=["POST"])
def envoyer_devis():
    DepartementModels().insert_devis(request.form["objet"], request.form["montant_estime"],
                                     request.form["fournisseur_id"], _user_id())
    return redirect("/departement/dashboard")

@departement_bp.route("/mes-devis")
def mes_devis():
    return render_template("departement/mes-devis.html", devis=DepartementModels().get_mes_devis(_user_id()))

@departement_bp.route("/bons-commande")
def bons_commande():
    return render_template("departement/mes-bons-commande.html", bons=DepartementModels().get_mes_bons_commande(_dep_id()))

@departement_bp.route("/mes-colis")
def mes_colis():
    return render_template("departement/mes-colis.html", colis=DepartementModels().get_colis_departement(_dep_id()))

@departement_bp.route("/budget")
def budget():
    dep_id = _dep_id()
    m = DepartementModels()
    return render_template("departement/budget.html",
                           budget=m.get_budget_departement(dep_id), depenses=m.get_depenses_departement(dep_id))

@departement_bp.route("/fournisseurs")
def fournisseurs():
    return render_template("departement/fournisseurs.html", fournisseurs=DepartementModels().get_fournisseurs_autorises())


# ===== FINANCE =====
finance_bp = Blueprint("finance", __name__, url_prefix="/finance")

@finance_bp.route("/dashboard")
def dashboard():
    m = FinanceModels()
    stats = {"devis_attente": m.count_devis_en_attente(), "bons_commande": m.count_bon_commande()}
    return render_template("finance/dashboard.html", stats=stats,
                           budgets=m.get_budgets_departements(), devis=m.get_devis_en_attente(),
                           bons=m.get_bons_commande_recents())

@finance_bp.route("/valider-devis")
def valider_devis():
    FinanceModels().valider_devis(request.args.get("id", type=int))
    return redirect("/finance/dashboard")

@finance_bp.route("/rejeter-devis")
def rejeter_devis():
    FinanceModels().rejeter_devis(request.args.get("id", type=int))
    return redirect("/finance/dashboard")

@finance_bp.route("/devis")
def devis_a_verifier():
    return render_template("finance/devis-a-verifier.html", devis=FinanceModels().get_devis_a_verifier())

@finance_bp.route("/bons-commande")
def bons_commande():
    return render_template("finance/bons-commande.html", bons=FinanceModels().get_tous_les_bons_commande())

@finance_bp.route("/budgets")
def budgets():
    return render_template("finance/budgets.html", budgets=FinanceModels().get_budget_departements())


# ===== DIRECTEUR =====
directeur_bp = Blueprint("directeur", __name__, url_prefix="/directeur")

@directeur_bp.route("/dashboard")
def dashboard():
    m = DirecteurModels()
    stats = {"devis_attente": m.count_devis_en_attente(), "bc_signes": m.count_bon_commande()}
    return render_template("directeur_iut/dashboard.html", stats=stats,
                           devis=m.get_devis_a_valider(), bons=m.get_bon_commande_signes())

@directeur_bp.route("/signer-devis")
def signer_devis():
    id = request.args.get("id", type=int)
    m = DirecteurModels()
    devis = m.get_devis_by_id(id)
    if not devis or devis["statut"] != "valide_finance": abort(400)
    if m.bon_commande_existe_pour_devis(id): abort(400)
    m.signer_devis(id)
    return redirect("/directeur/dashboard")

@directeur_bp.route("/devis")
def devis_a_signer():
    return render_template("directeur_iut/devis-a-signer.html", devis=DirecteurModels().get_devis_a_valider())

@directeur_bp.route("/bons-commande")
def bons_commande():
    return render_template("directeur_iut/bons-commande.html", bons=DirecteurModels().get_tous_les_bons_commande())
