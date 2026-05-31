import os
import sys
sys.path.insert(0, os.path.dirname(__file__))

from flask import Flask, session, redirect, request, render_template
from flask_session import Session
from config.config import Config
from controllers.routes import (admin_bp, postal_iut_bp, postal_univ_bp,
                                  departement_bp, finance_bp, directeur_bp)

app = Flask(__name__)
app.config.from_object(Config)
app.secret_key = Config.SECRET_KEY

# Sessions serveur
Session(app)

# Enregistrement des blueprints
app.register_blueprint(admin_bp)
app.register_blueprint(postal_iut_bp)
app.register_blueprint(postal_univ_bp)
app.register_blueprint(departement_bp)
app.register_blueprint(finance_bp)
app.register_blueprint(directeur_bp)

ROLE_REDIRECTS = {
    "admin":       "/admin/dashboard",
    "postal_iut":  "/postal/dashboard",
    "postal_univ": "/postal-univ/dashboard",
    "finance":     "/finance/dashboard",
    "directeur":   "/directeur/dashboard",
    "departement": "/departement/dashboard",
}

PUBLIC_ROUTES = ["/", "/login", "/dev-login", "/logout"]

# ===== MIDDLEWARE AUTH =====
@app.before_request
def check_auth():
    if request.path.startswith("/static"):
        return
    if request.path in PUBLIC_ROUTES:
        return
    if "user" not in session:
        if Config.ENV == "development":
            return redirect("/dev-login")
        return redirect("/login")

# ===== ROUTES AUTH =====
@app.route("/")
def index():
    if "user" not in session:
        return redirect("/dev-login" if Config.ENV == "development" else "/login")
    role = session["user"].get("role", "departement")
    return redirect(ROLE_REDIRECTS.get(role, "/departement/dashboard"))

@app.route("/login")
def login():
    if "user" in session:
        role = session["user"].get("role", "departement")
        return redirect(ROLE_REDIRECTS.get(role, "/departement/dashboard"))
    if Config.ENV == "development":
        return redirect("/dev-login")
    # En production : affichage de la page CAS
    if request.args.get("auth") == "cas":
        # TODO: implémenter l'authentification CAS ici
        return redirect("/dev-login")
    return render_template("login.html")

@app.route("/dev-login", methods=["GET", "POST"])
def dev_login():
    if Config.ENV != "development":
        return redirect("/login")

    from config.database import get_db
    error = None
    utilisateurs = []

    try:
        with get_db().cursor() as c:
            c.execute("""SELECT u.id_utilisateur, u.uid_cas, u.fullName, u.email,
                                r.libelle as role, u.departement_id
                         FROM utilisateur u JOIN role r ON u.role_id=r.id_role
                         ORDER BY r.libelle, u.fullName""")
            utilisateurs = c.fetchall()
    except Exception as e:
        error = str(e)

    if request.method == "POST":
        uid = request.form.get("uid") or request.form.get("uid_cas")
        role_form = request.form.get("role", "departement")
        if not uid:
            error = "Veuillez entrer un identifiant."
        else:
            try:
                with get_db().cursor() as c:
                    c.execute("""SELECT u.id_utilisateur, u.uid_cas, u.fullName, u.email,
                                        r.libelle as role, u.departement_id
                                 FROM utilisateur u JOIN role r ON u.role_id=r.id_role
                                 WHERE u.uid_cas=%s""", (uid,))
                    user = c.fetchone()
                if user:
                    session["user"] = user
                    return redirect(ROLE_REDIRECTS.get(user["role"], "/departement/dashboard"))
                session["user"] = {
                    "id_utilisateur": None, "uid_cas": uid,
                    "fullName": "Dev User - " + uid, "email": uid + "@dev.local",
                    "role": role_form, "departement_id": None,
                }
                return redirect(ROLE_REDIRECTS.get(role_form, "/departement/dashboard"))
            except Exception as e:
                error = str(e)

    return render_template("dev-login.html", utilisateurs=utilisateurs, error=error)

@app.route("/logout")
def logout():
    session.clear()
    return redirect("/dev-login" if Config.ENV == "development" else "/login")

# ===== ERREURS =====
@app.errorhandler(404)
def not_found(e):
    return render_template("errors/404.html"), 404

@app.errorhandler(403)
def forbidden(e):
    return render_template("errors/403.html"), 403

@app.errorhandler(500)
def server_error(e):
    return render_template("errors/500.html", error=str(e)), 500

if __name__ == "__main__":
    app.run(debug=Config.DEBUG, port=5000)
