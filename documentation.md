```markdown
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
    ├── .env / .env.example / .gitignore
    ├── public/
    │   ├── index.php            # point d'entrée Slim + routes
    │   ├── .htaccess
    │   └── assets/ (style.css, app.js)
    ├── src/
    │   ├── Controllers/
    │   │   ├── LibraryController.php   # saisie du pseudo + liste des jeux
    │   │   └── GameController.php      # checklist, progression, lien wiki
    │   ├── Models/
    │   │   ├── Game.php
    │   │   └── Achievement.php
    │   └── Services/
    │       └── SteamService.php        # tous les appels à l'API Steam
    └── views/ (layout.php, library.php, game.php)