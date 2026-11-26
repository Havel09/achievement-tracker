<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SteamService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Slim\Views\PhpRenderer;

/**
 * LibraryController : la page d'accueil.
 * Elle affiche le formulaire du pseudo et, si un pseudo est donné, la bibliothèque du joueur.
 */
class LibraryController
{
    // Le contrôleur reçoit ce dont il a besoin à sa création (voir public/index.php)
    public function __construct(
        private SteamService $steam, // pour interroger Steam
        private PhpRenderer $view    // pour afficher les vues
    ) {
    }

    // Route : GET /  (ou /?pseudo=... quand le formulaire est envoyé)
    public function index(Request $request, Response $response): Response
    {
        // Ce que la personne a tapé dans le formulaire
        $pseudo = trim($request->getQueryParams()['pseudo'] ?? '');

        // Valeurs envoyées à la vue : par défaut, aucun joueur, aucun jeu, aucune erreur
        $steamId = '';
        $games = [];
        $error = null;

        // Si un pseudo est donné, on va chercher la bibliothèque
        if ($pseudo !== '') {
            try {
                $steamId = $this->steam->resolveVanityUrl($pseudo);
                $games = $this->steam->getOwnedGames($steamId);

                // On retient le joueur pour le lien « Ma bibliothèque » de l'en-tête
                $_SESSION['steamId'] = $steamId;

                // Tri par ordre alphabétique : $a et $b sont deux jeux à comparer,
                // strcasecmp compare leurs noms sans tenir compte des majuscules
                usort($games, function ($a, $b) {
                    return strcasecmp($a->name, $b->name);
                });
            } catch (RuntimeException $e) {
                // Pseudo inconnu, profil privé... : le message sera affiché par la vue
                $error = $e->getMessage();
            }
        }

        // On affiche la vue views/library.php avec ces valeurs
        return $this->view->render($response, 'library.php', [
            'pseudo' => $pseudo,
            'steamId' => $steamId,
            'games' => $games,
            'error' => $error,
        ]);
    }
}
