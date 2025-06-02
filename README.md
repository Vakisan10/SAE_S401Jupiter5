# SAE S4.01 — Système de gestion des colis et commandes
## IUT de Villetaneuse — Université Sorbonne Paris Nord

Plateforme de suivi des colis, demandes d'achat, devis et bons de commande.

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | Python 3.12 + Flask |
| Base de données | SQLite |
| Authentification | JWT (flask-jwt-extended) |
| Frontend | React 18 + Vite + Tailwind CSS |

## Architecture

```
SAE_S401Jupiter5/
├── app/                    # Backend Flask
│   ├── controller/         # Routes API REST
│   ├── service/            # Logique métier
│   ├── dao/                # Accès base de données
│   ├── model/              # Modèles de données
│   ├── database/           # Schéma SQL + init
│   └── static/             # Fichiers statiques (QR codes)
├── frontend/               # Frontend React
│   └── src/
│       ├── components/     # Composants réutilisables
│       ├── pages/          # Pages de l'application
│       ├── services/       # Appels API
│       ├── context/        # Contextes React (auth...)
│       └── hooks/          # Hooks personnalisés
├── config.py               # Configuration Flask
├── run.py                  # Point d'entrée
└── requirements.txt        # Dépendances Python
```

## Lancement

### Backend
```bash
pip install -r requirements.txt
python run.py
```

### Frontend
```bash
cd frontend
npm install
npm run dev
```

## Membres du groupe
- Jupiter 5
