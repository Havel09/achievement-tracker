<?php

/*
 * Point d'entrée du site : toutes les pages passent par ce fichier (grâce au .htaccess).
 * Son rôle : préparer l'application Slim, créer les objets, puis déclarer les routes.
 */

declare(strict_types=1);

use App\Controllers\GameController;
use App\Controllers\LibraryController;
use App\Services\SteamService;
use Dotenv\Dotenv;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

// Charge toutes les librairies installées par Composer (Slim, phpdotenv...)
require __DIR__ . '/../vendor/autoload.php';

/**
 * Affiche un texte sans risque dans une page HTML.
 * Elle transforme les caractères spéciaux (< > " ...) pour qu'un texte venant de Steam
 * ou de l'URL ne puisse pas casser la page. Elle est utilisée dans toutes les vues.
 */
function echapper(string $texte): string
{
    return htmlspecialchars($texte, ENT_QUOTES, 'UTF-8');
}

// Les sessions servent à retenir le dernier joueur consulté (lien « Ma bibliothèque »)
session_start();

// Dossier racine du projet (le dossier au-dessus de public/)
$root = dirname(__DIR__);

// Lit le fichier .env : clé API Steam, mode debug, fuseau horaire
Dotenv::createImmutable($root)->safeLoad();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Zurich');

// APP_DEBUG=true dans le .env : les erreurs sont affichées en détail (pour développer)
$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);

$app = AppFactory::create();
$app->addRoutingMiddleware(); // choisit la route selon l'URL
$errorMiddleware = $app->addErrorMiddleware($debug, true, true); // gère les erreurs

// Les vues sont dans views/, et chaque page est placée dans layout.php
$view = new PhpRenderer($root . '/views', [], 'layout.php');

$steam = new SteamService($_ENV['STEAM_API_KEY'] ?? '');

$library = new LibraryController($steam, $view);
$gameController = new GameController($steam, $view);

$errorMiddleware->setErrorHandler(HttpNotFoundException::class, function () use ($app, $view) {
    $response = $app->getResponseFactory()->createResponse(404);

    return $view->render($response, '404.php');
});

// Accueil : formulaire du pseudo + liste des jeux
$app->get('/', [$library, 'index']);

// Page d'un jeu. Dans l'adresse, le steamId fait 17 chiffres et l'appId est un nombre.
// Exemple : /game/76561199248850149/105600
$app->get('/game/{steamId:[0-9]{17}}/{appId:[0-9]+}', [$gameController, 'show']);

// Lance l'application : Slim lit l'URL, trouve la route et envoie la réponse
$app->run();
