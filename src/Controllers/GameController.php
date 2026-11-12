<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SteamService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Slim\Views\PhpRenderer;

// Page d'un jeu : checklist des succès, progression et lien vers le wiki.
class GameController
{
    public function __construct(private SteamService $steam, private PhpRenderer $view) { }

    // URL : /game/{steamId}/{appId}
    public function show(Request $request, Response $response, array $args): Response
    {
        $steamId = $args['steamId'];
        $appId = (int) $args['appId'];
        $game = null;
        $error = null;

        try {
            // On retrouve le jeu dans la bibliothèque du joueur
            foreach ($this->steam->getOwnedGames($steamId) as $owned) {
                if ($owned->appId === $appId) {
                    $game = $owned;
                    break;
                }
            }

            if ($game === null) {
                $error = 'Jeu introuvable dans cette bibliothèque.';
            } else {
                // On ajoute les succès au jeu : getProgress() peut alors calculer le %
                $game->achievements = $this->steam->getAchievements($steamId, $appId);
            }
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        return $this->view->render($response, 'game.php', [
            'steamId' => $steamId,
            'game' => $game,
            'error' => $error,
        ]);
    }
}
