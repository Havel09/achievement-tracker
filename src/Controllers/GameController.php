<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SteamService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Slim\Views\PhpRenderer;

/**
 * GameController : la page d'un jeu.
 * Elle prépare les informations du jeu et sa checklist de succès.
 */
class GameController
{
    // Le contrôleur reçoit ce dont il a besoin à sa création (voir public/index.php)
    public function __construct(
        private SteamService $steam, // pour interroger Steam
        private PhpRenderer $view // pour afficher les vues
    ) {
    }

    // Route : GET /game/{steamId}/{appId}
    public function show(Request $request, Response $response, array $args): Response
    {
        // Les deux valeurs contenues dans l'adresse
        $steamId = $args['steamId'];
        $appId = (int) $args['appId'];

        // Valeurs envoyées à la vue : par défaut, pas de jeu, pas d'erreur, aucun succès
        $game = null;
        $error = null;
        $total = 0; // nombre de succès du jeu
        $unlocked = 0; // nombre de succès débloqués

        try {
            // On retrouve le jeu dans la bibliothèque du joueur (pour son nom, son icône...)
            foreach ($this->steam->getOwnedGames($steamId) as $ownedGame) {
                if ($ownedGame->appId === $appId) {
                    $game = $ownedGame;
                }
            }

            if ($game === null) {
                throw new RuntimeException('Jeu introuvable dans cette bibliothèque.');
            }

            // On ajoute les succès au jeu (ce qui permet à getProgress() de calculer le %)
            $game->achievements = $this->steam->getAchievements($steamId, $appId);

            $total = count($game->achievements);

            foreach ($game->achievements as $achievement) {
                if ($achievement->unlocked) {
                    $unlocked++;
                }
            }
        } catch (RuntimeException $e) {
            // Jeu introuvable, profil privé... : le message sera affiché par la vue
            $error = $e->getMessage();
        }

        // On affiche la vue views/game.php avec ces valeurs
        return $this->view->render($response, 'game.php', [
            'steamId' => $steamId,
            'game' => $game,
            'error' => $error,
            'total' => $total,
            'unlocked' => $unlocked,
        ]);
    }
}
