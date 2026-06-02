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
├── app/
│   ├── controller/
│   ├── service/
│   ├── dao/
│   ├── model/
│   ├── database/
│   └── static/
├── frontend/
│   └── src/
│       ├── components/
│       ├── pages/
│       ├── services/
│       ├── context/
│       └── hooks/
├── config.py
├── run.py
└── requirements.txt
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
