# Nom du projet : achievement-tracker

## Description
le but du projet est de faire un tracker pour les succès steam qui récupère les succès d'une personne afin de les afficher sous forme de checklist en ayant les infos du succès aussi, 
on aurait donc notre bibliothèque récupéré et affiché pour nous aider à la completion d'un jeu.

## Problème résolu avec ce projet : 
Un site web permettant de naviguer dans ses succès steam facilement afin de mieux suivre sa progression.

## Fonction à implémenter : 
* Récupération des informations d'un utilisateur avec la clé api steam
* Voir la progression globale des succès
* Voir les succès d'un jeu et leur description
* Avoir un lien directement sur le wiki du jeu sélectionné

## Arborescence
    achievement-tracker/
    ├── composer.json
    ├── .gitignore                    # vendor/, cache/, config/secrets.php
    │
    ├── public/                       # Seul dossier exposé par le serveur web
    │   ├── index.php                 # Point d'entrée Slim (crée l'app, charge les routes)
    │   ├── .htaccess                 # Fichier existant -> servi, sinon -> index.php
    │   ├── index.html                # Saisie du SteamID / pseudo
    │   ├── library.html              # Bibliothèque de jeux
    │   ├── game.html                 # Checklist des succès
    │   └── assets/
    │       ├── css/ (style.css, library.css, game.css)
    │       └── js/  (api.js, storage.js, library.js, game.js, utils.js)
    │
    ├── config/
    │   ├── settings.php              # Chemins, durée du cache
    │   └── secrets.php               # Clé API Steam (non versionné)
    │
    ├── routes/
    │   └── api.php                   # Toutes les routes /api/...
    │
    ├── src/
    │   ├── Controllers/              # C : reçoivent la requête, renvoient du JSON
    │   │   ├── UserController.php
    │   │   ├── GameController.php
    │   │   └── AchievementController.php
    │   ├── Models/                   # M : représentent les données
    │   │   ├── Game.php
    │   │   └── Achievement.php
    │   └── Services/                 # Logique externe (appels Steam, cache)
    │       ├── SteamService.php
    │       └── CacheService.php
    │
    ├── cache/                        # Réponses Steam en JSON
    └── vendor/                       # Généré par Composer
