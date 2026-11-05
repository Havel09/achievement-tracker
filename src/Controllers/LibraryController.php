<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SteamService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Slim\Views\PhpRenderer;

// Page d'accueil : saisie du pseudo + liste des jeux de la bibliothèque.
class LibraryController
{
    public function __construct(
        private SteamService $steam,
        private PhpRenderer $view
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        // Le pseudo arrive dans l'URL : /?pseudo=bigradius1961
        $pseudo = trim($request->getQueryParams()['pseudo'] ?? '');
        $steamId = '';
        $games = [];
        $error = null;

        if ($pseudo !== '') {
            try {
                $steamId = $this->steam->resolveVanityUrl($pseudo);
                $games = $this->steam->getOwnedGames($steamId);

                // Tri par ordre alphabétique
                usort($games, fn($a, $b) => strcasecmp($a->name, $b->name));
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            }
        }

        return $this->view->render($response, 'library.php', [
            'pseudo' => $pseudo,
            'steamId' => $steamId,
            'games' => $games,
            'error' => $error,
        ]);
    }
}
