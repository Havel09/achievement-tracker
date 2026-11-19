<?php

declare(strict_types=1);

use App\Controllers\GameController;
use App\Controllers\LibraryController;
use App\Services\SteamService;
use Dotenv\Dotenv;  
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Exception\HttpNotFoundException;

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);

Dotenv::createImmutable($root)->safeLoad();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Zurich');

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL), true, true);

$view = new PhpRenderer($root . '/views', [], 'layout.php');
$steam = new SteamService($_ENV['STEAM_API_KEY'] ?? '');
$library = new LibraryController($steam, $view);
$gameController = new GameController($steam, $view);
// Page 404 personnalisée
$errorMiddleware->setErrorHandler(HttpNotFoundException::class, function () use ($app, $view) {
    $response = $app->getResponseFactory()->createResponse(404);

    return $view->render($response, '404.php');
});

// Page d'accueil : saisie du pseudo + bibliothèque
$app->get('/', [$library, 'index'])->setName('home');

// Page d'un jeu. Le steamId doit faire 17 chiffres et l'appId doit être un nombre.
$app->get('/game/{steamId:[0-9]{17}}/{appId:[0-9]+}', [$gameController, 'show']);


$app->run();