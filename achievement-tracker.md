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
    ├── public/
    │   ├── index.php 
    │   ├── .htaccess
    │   └── assets/
    │       ├── css/ (style.css, library.css, game.css)
    │       └── js/  (filters.js, progress.js, storage.js)
    │
    ├── src/
    │   ├── Config/
    │   │   └── env.php
    │   │
    │   ├── Models/
    │   │   ├── Player.php
    │   │   ├── Game.php
    │   │   └── Achievement.php
    │   │
    │   ├── Services/
    │   │   ├── SteamService.php
    │   │   └── CacheService.php
    │   │
    │   ├── Controllers/
    │   │   ├── HomeController.php
    │   │   ├── LibraryController.php
    │   │   └── GameController.php
    │   │
    │   └── Views/
    │       ├── layouts/
    │       │   └── main.php
    │       ├── home.php
    │       ├── library.php
    │       ├── game.php
    │       └── partials/
    │           ├── game_card.php
    │           ├── achievement_item.php
    │           └── progress_bar.php
    │
    ├── routes/
    │   └── web.php
    │
    ├── config/
    │   ├── env/
    │   │   └── .env.example
    │   └── vhost/
    │       └── achievement-tracker.local.conf
    │
    ├── cache/
    ├── .env
    ├── .gitignore
    ├── composer.json
    └── README.md
