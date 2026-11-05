<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use App\Controllers\LibraryController;
use App\Services\SteamService;
use Slim\Views\PhpRenderer;

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);

Dotenv::createImmutable($root)->safeLoad();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Zurich');

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);
$app->addErrorMiddleware($debug, true, true);

$view = new PhpRenderer($root . '/views', [], 'layout.php');
$steam = new SteamService($_ENV['STEAM_API_KEY'] ?? '');
$library = new LibraryController($steam, $view);

$app->get('/', [$library, 'index'])->setName('home');

$app->run();
