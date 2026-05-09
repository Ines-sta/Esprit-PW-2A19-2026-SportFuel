# SportFuel - Guide runtime actuel

Base locale recommandee : `http://localhost/Esprit-PW-2A19-2526-SportFuel`

## Entrees principales

| Usage | URL |
|------|-----|
| Landing page statique | http://localhost/Esprit-PW-2A19-2526-SportFuel/View/index.html |
| Connexion | http://localhost/Esprit-PW-2A19-2526-SportFuel/View/connexion.html |
| Profil sportif | http://localhost/Esprit-PW-2A19-2526-SportFuel/View/profil.html |
| Routeur plans FO/BO | http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php |
| BackOffice canonique | http://localhost/Esprit-PW-2A19-2526-SportFuel/BackOffice/index.php |

## Plans alimentaires

### FrontOffice
| Page | URL |
|------|-----|
| Accueil plans | http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php |
| Liste des plans | http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php?page=plans |
| Detail plan | http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php?page=detail&id=1 |

### BackOffice
| Action | URL |
|--------|-----|
| Liste des plans | http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php?page=back&action=listPlans |
| Ajouter un plan | http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php?page=back&action=addPlan |
| Modifier un plan | http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php?page=back&action=updatePlan&id=1 |
| Liste des repas | http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php?page=back&action=listRepas |
| Ajouter un repas | http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php?page=back&action=addRepas |
| Modifier un repas | http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php?page=back&action=updateRepas&id=1 |

## Sources canoniques actuelles

| Domaine | Fichier / dossier canonique |
|--------|------------------------------|
| Routeur plans | `index.php` |
| Plans BO | `BackOffice/controllers/PlanAlimentaireController.php` |
| Repas BO | `BackOffice/controllers/RepasController.php` |
| Vues plans BO | `BackOffice/views/plans/` |
| Vues plans FO | `FrontOffice/views/plans/` |
| Social DB bridge | `BackOffice/models/Database.php` |
| Schema consolide | `database/schema.sql` |
| Migrations | `database/migrations/` |

## Archives de migration

Les anciens fichiers remplaces pendant la migration sont conserves sous `archive/`.
Ils ne doivent plus etre consideres comme source runtime active.

## Structure simplifiee actuelle

```
Esprit-PW-2A19-2526-SportFuel/
|- index.php
|- config.php
|- database/
|  |- schema.sql
|  \- migrations/
|- BackOffice/
|  |- controllers/
|  |- models/
|  |- partials/
|  |- views/
|  \- index.php
|- FrontOffice/
|  |- controllers/
|  |- models/
|  |- partials/
|  \- views/
|- Controller/
|- Model/
|- View/
\- archive/
```
