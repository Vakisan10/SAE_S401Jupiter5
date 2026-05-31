# SAE Colis – Version Python Flask

## Installation

```bash
cd python_app
pip install -r requirements.txt
```

## Configuration

```bash
cp .env.example .env
# Editer .env avec vos infos DB
```

## Lancer le serveur

```bash
python app.py
```

Ouvrir http://localhost:5000

## Structure

```
python_app/
├── app.py               # Point d'entrée Flask
├── config/
│   ├── config.py        # Configuration (.env)
│   └── database.py      # Connexion MySQL
├── controllers/
│   └── routes.py        # Tous les blueprints (admin, postal, finance...)
├── models/
│   ├── admin_models.py
│   ├── postal_iut_models.py
│   └── other_models.py  # postal_univ, departement, finance, directeur
├── templates/           # Jinja2 (équivalent views PHP)
│   ├── base.html
│   ├── admin/
│   ├── postal_iut/
│   ├── postal_univ/
│   ├── departement/
│   ├── finance/
│   └── directeur_iut/
├── static/css/          # CSS (même que PHP)
└── requirements.txt
```

## Équivalences PHP → Python

| PHP | Python |
|-----|--------|
| index.php + Router | app.py + Blueprints Flask |
| Controllers/*.php | controllers/routes.py (Blueprints) |
| Models/*.php | models/*.py |
| Views/*.php | templates/*.html (Jinja2) |
| $_SESSION | flask.session |
| $_POST / $_GET | request.form / request.args |
| header("Location: ...") | redirect(...) |
| require_once view.php | render_template("view.html") |
