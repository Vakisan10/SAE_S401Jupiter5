# Architecture MVC — SAE_S401Jupiter5

## Structure src/public/

```
src/public/
│
├── index.php                          ← Front controller (point d'entrée)
├── router.php                         ← Routeur HTTP
│
├── models/                            ← Entités & connexion BDD
│   ├── Model.php                      ← Singleton PDO (connexion)
│   ├── Colis.php                      ← Entité colis (constructeur array)
│   ├── AdminModels.php                ← À refactorer (mélange DAO+Service)
│   ├── DepartementModels.php          ← À refactorer
│   ├── DirecteurModels.php            ← À refactorer
│   ├── FinanceModels.php              ← À refactorer
│   ├── PostalIutModels.php            ← À refactorer → remplacé par ColisDAO
│   ├── PostalUnivModels.php           ← À refactorer
│   └── UserRepository.php             ← OK (déjà propre)
│
├── dao/                               ← Requêtes SQL uniquement
│   └── ColisDAO.php                   ← CRUD colis (findAll, findById, insert...)
│
├── services/                          ← Logique métier
│   ├── ColisService.php               ← Actions sur les colis (validation, statuts)
│   ├── ScannerService.php             ← Traitement scan QR
│   └── PdfGenerator.php              ← Génération PDF
│
├── controllers/                       ← Traitement des requêtes HTTP
│   ├── PostalIutController.php        ← À refactorer (utilise encore Models)
│   ├── PostalUnivController.php       ← À refactorer
│   ├── ScannerController.php          ← Scanner QR
│   ├── AdminController.php
│   ├── DepartementController.php
│   ├── DirecteurController.php
│   └── FinanceController.php
│
├── assets/
│   ├── css/
│   ├── img/
│   └── js/
│       └── scanner.js                 ← Accès caméra + décodage jsQR
│
└── views/                             ← Templates PHP (HTML)
    ├── postal-iut/
    │   ├── dashboard.php
    │   ├── ajouter-colis.php
    │   ├── colis-recus.php
    │   ├── colis-attente.php
    │   ├── colis-remis.php
    │   ├── colis-details.php
    │   ├── colis-non-identifies.php
    │   ├── confirmation.php
    │   ├── modifier-colis.php
    │   ├── recherche-colis.php
    │   └── historique.php
    ├── postal-univ/
    │   ├── dashboard.php
    │   ├── reception-colis.php
    │   ├── colis.php
    │   ├── non-identifies.php
    │   └── historique.php
    ├── admin/
    │   ├── dashboard.php
    │   ├── utilisateurs.php
    │   ├── fournisseurs.php
    │   ├── ajouter-fournisseur.php
    │   ├── modifier-fournisseur.php
    │   ├── departements.php
    │   ├── ajouter-departement.php
    │   ├── modifier-departement.php
    │   ├── colis.php
    │   ├── commandes.php
    │   └── devis.php
    ├── departement/
    │   ├── dashboard.php
    │   ├── creer-devis.php
    │   ├── mes-devis.php
    │   ├── mes-bons-commande.php
    │   ├── mes-colis.php
    │   ├── budget.php
    │   └── fournisseurs.php
    ├── finance/
    │   ├── dashboard.php
    │   ├── devis-a-verifier.php
    │   ├── valider-devis.php
    │   ├── rejeter-devis.php
    │   ├── bons-commande.php
    │   └── budgets.php
    ├── directeur-iut/
    │   ├── dashboard.php
    │   ├── devis-a-signer.php
    │   ├── devis-signature.php
    │   ├── signer-devis.php
    │   ├── voir-devis.php
    │   └── bons-commande.php
    └── scanner/
        └── scan.php
```

## Flux d'une requête

```
Navigateur
    ↓
index.php  (auth CAS + routing)
    ↓
Controller  (reçoit la requête HTTP)
    ↓
Service     (logique métier, validation)
    ↓
DAO         (requêtes SQL)
    ↓
Entité      (objet Colis, User...)
    ↓
BDD (MySQL)
    ↑
View        (rendu HTML depuis le Controller)
```

## Statuts colis (BDD)

| ID | Libellé            |
|----|--------------------|
| 1  | recu_universite    |
| 2  | transfere_iut      |
| 3  | en_attente         |
| 4  | livre              |

## Prochaines étapes

1. Refactorer `PostalIutController` → utiliser `ColisService` au lieu de `PostalIutModels`
2. Brancher les routes scanner dans `index.php`
3. Implémenter les notifications (table existe en BDD, aucune vue)
4. Ajouter pagination sur les listes
5. Ajouter export CSV/PDF via `PdfGenerator`
