<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);

Dotenv::createImmutable($root)->safeLoad();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Zurich');

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);
$app->addErrorMiddleware($debug, true, true);

$app->get('/', function (Request $request, Response $response): Response {
    $html = <<<'HTML'
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>achievement-tracker</title>
            <style>
                body { font-family: system-ui, sans-serif; max-width: 42rem; margin: 4rem auto; padding: 0 1rem; line-height: 1.6; }
                code { background: #f1f5f9; padding: .15rem .35rem; border-radius: .25rem; }
            </style>
        </head>
        <body>
            <h1>achievement-tracker</h1>
            <p>Le socle Slim fonctionne.</p>
            <p>Route de test : <a href="/test"><code>/test</code></a></p>
        </body>
        </html>
        HTML;

    $response->getBody()->write($html);

    return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
})->setName('home');

$app->get('/test', function (Request $request, Response $response): Response {
    $apiKey = trim((string) ($_ENV['STEAM_API_KEY'] ?? ''));

    $payload = [
        'status' => 'ok',
        'application' => 'achievement-tracker',
        'php' => PHP_VERSION,
        'slim' => \Slim\App::VERSION,
        'environment' => $_ENV['APP_ENV'] ?? 'production',
        'steam_api_key' => $apiKey !== '' ? 'configuree' : 'manquante',
        'timezone' => date_default_timezone_get(),
        'date' => date(DATE_ATOM),
    ];

    $response->getBody()->write(
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );

    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
})->setName('test');

$app->run();
