# SportFuel — Guide des URLs

> Base : `http://localhost/SportFuel`

---

## FrontOffice

| Page | URL |
|------|-----|
| Accueil | http://localhost/SportFuel |
| Liste des plans | http://localhost/SportFuel?page=plans |
| Détail plan #1 | http://localhost/SportFuel?page=detail&id=1 |
| Détail plan #2 | http://localhost/SportFuel?page=detail&id=2 |
| Détail plan #3 | http://localhost/SportFuel?page=detail&id=3 |

---

## BackOffice — Plans alimentaires

| Action | URL |
|--------|-----|
| Dashboard / Liste des plans | http://localhost/SportFuel?page=back&action=listPlans |
| Ajouter un plan | http://localhost/SportFuel?page=back&action=addPlan |
| Modifier plan #1 | http://localhost/SportFuel?page=back&action=updatePlan&id=1 |
| Modifier plan #2 | http://localhost/SportFuel?page=back&action=updatePlan&id=2 |
| Supprimer plan #1 | http://localhost/SportFuel?page=back&action=deletePlan&id=1 |

---

## BackOffice — Repas

| Action | URL |
|--------|-----|
| Liste de tous les repas | http://localhost/SportFuel?page=back&action=listRepas |
| Repas du plan #1 | http://localhost/SportFuel?page=back&action=listRepas&id_plan=1 |
| Repas du plan #2 | http://localhost/SportFuel?page=back&action=listRepas&id_plan=2 |
| Ajouter un repas | http://localhost/SportFuel?page=back&action=addRepas |
| Modifier repas #1 | http://localhost/SportFuel?page=back&action=updateRepas&id=1 |
| Modifier repas #2 | http://localhost/SportFuel?page=back&action=updateRepas&id=2 |
| Supprimer repas #1 | http://localhost/SportFuel?page=back&action=deleteRepas&id=1 |

---

## IDs disponibles en base de données

### Plans (id 1 → 6)
| ID | Nom |
|----|-----|
| 1  | Plan musculation semaine 1 |
| 2  | Programme minceur printemps |
| 3  | Maintien forme été |
| 4  | Marathon préparation |
| 5  | Prise de masse avancée |
| 6  | Sèche compétition |

### Repas (id 1 → 14)
| ID | Jour | Type |
|----|------|------|
| 1  | Lundi | petit_dejeuner |
| 2  | Lundi | dejeuner |
| 3  | Lundi | diner |
| 4  | Lundi | collation |
| 5  | Mardi | petit_dejeuner |
| 6  | Mardi | dejeuner |
| 7  | Mardi | diner |
| 8  | Mercredi | petit_dejeuner |
| 9  | Mercredi | dejeuner |
| 10 | Jeudi | petit_dejeuner |
| 11 | Jeudi | dejeuner |
| 12 | Jeudi | collation |
| 13 | Vendredi | petit_dejeuner |
| 14 | Vendredi | dejeuner |

---

## Structure des fichiers

```
SportFuel/
├── index.php                         ← Routeur principal
├── config.php                        ← Connexion PDO
├── sportfuel.sql                     ← Import base de données
├── GUIDE.md                          ← Ce fichier
├── Model/
│   ├── PlanAlimentaire.php
│   └── Repas.php
├── Controller/
│   ├── PlanAlimentaireController.php
│   └── RepasController.php
├── View/
│   ├── FrontOffice/
│   │   ├── index.php
│   │   ├── plans.php
│   │   └── detailPlan.php
│   ├── BackOffice/
│   │   ├── listPlans.php
│   │   ├── addPlan.php
│   │   ├── updatePlan.php
│   │   ├── listRepas.php
│   │   ├── addRepas.php
│   │   └── updateRepas.php
│   └── partials/
│       ├── topbar.php
│       ├── sidebar.php
│       └── footer.php
└── public/
    ├── css/style.css
    └── js/
        ├── addPlan.js
        └── addRepas.js
```
