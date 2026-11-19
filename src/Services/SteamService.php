<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Achievement;
use App\Models\Game;
use RuntimeException;

// Tous les appels à l'API Steam passent par cette classe.
class SteamService
{
    public function __construct(private string $apiKey)
    {
    }

    // transforme un pseudo Steam (vanity URL) en SteamID.
    public function resolveVanityUrl(string $name): string
    {
        $name = trim($name);

        // Déjà un SteamID (17 chiffres) : pas besoin d'appeler Steam
        if (preg_match('/^\d{17}$/', $name)) {
            return $name;
        }

        $data = $this->call('ISteamUser/ResolveVanityURL/v0001/', ['vanityurl' => $name]);

        // success = 1 : profil trouvé, 42 : aucun profil avec ce pseudo
        if (($data['response']['success'] ?? 0) !== 1) {
            throw new RuntimeException("Aucun profil Steam trouvé pour « $name ».");
        }

        return $data['response']['steamid'];
    }

    // récupère la bibliothèque de jeux d'un joueur.
    /** @return Game[] */
    public function getOwnedGames(string $steamId): array
    {
        $data = $this->call('IPlayerService/GetOwnedGames/v0001/', [
            'steamid' => $steamId,
            'include_appinfo' => 1, // nom et icône de chaque jeu
            'include_played_free_games' => 1, // jeux gratuits compris
        ]);

        // Profil privé : Steam renvoie une réponse vide
        if (!isset($data['response']['games'])) {
            throw new RuntimeException('Bibliothèque introuvable : le profil est peut-être privé.');
        }

        $games = [];

        foreach ($data['response']['games'] as $g) {
            $iconUrl = empty($g['img_icon_url'])
                ? ''
                : "https://media.steampowered.com/steamcommunity/public/images/apps/{$g['appid']}/{$g['img_icon_url']}.jpg";

            $games[] = new Game($g['appid'], $g['name'] ?? 'Jeu inconnu', $iconUrl, $g['playtime_forever'] ?? 0);
        }

        return $games;
    }

    // récupère les succès d'un jeu, avec leur description et s'ils sont débloqués.
    /** @return Achievement[] */
    public function getAchievements(string $steamId, int $appId): array
    {
        // 1. Ce que le joueur a débloqué
        $data = $this->call('ISteamUserStats/GetPlayerAchievements/v0001/', [
            'steamid' => $steamId,
            'appid' => $appId,
        ]);
        $stats = $data['playerstats'] ?? [];

        if (!($stats['success'] ?? false)) {
            $error = $stats['error'] ?? 'Réponse invalide de Steam.';

            // Un jeu sans succès n'est pas une erreur : liste vide
            if ($error === 'Requested app has no stats') {
                return [];
            }

            throw new RuntimeException($error);
        }

        // Tableau [nom du succès => 1 si débloqué, 0 sinon]
        $unlocked = array_column($stats['achievements'] ?? [], 'achieved', 'apiname');

        // 2. La liste complète des succès du jeu (nom, description, icônes), en français
        $schema = $this->call('ISteamUserStats/GetSchemaForGame/v2/', ['appid' => $appId, 'l' => 'french']);
        $achievements = [];

        foreach ($schema['game']['availableGameStats']['achievements'] ?? [] as $s) {
            $isUnlocked = (bool) ($unlocked[$s['name']] ?? false);

            $achievements[] = new Achievement(
                $s['name'],
                $s['displayName'] ?? $s['name'],
                $s['description'] ?? '',
                $isUnlocked ? ($s['icon'] ?? '') : ($s['icongray'] ?? ''), // icône grise si pas débloqué
                (bool) ($s['hidden'] ?? false),
                $isUnlocked
            );
        }

        return $achievements;
    }

    // Fait un appel à l'API Steam et renvoie la réponse JSON sous forme de tableau.
    private function call(string $endpoint, array $params): array
    {
        $params['key'] = $this->apiKey;
        $url = 'https://api.steampowered.com/' . $endpoint . '?' . http_build_query($params);

        // ignore_errors : on veut lire la réponse même quand Steam répond 400 ou 403.
        $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 10]]);
        $json = @file_get_contents($url, false, $context);

        if ($json === false) {
            throw new RuntimeException("Impossible de contacter l'API Steam.");
        }

        $data = json_decode($json, true);

        // Quand la clé API est refusée, Steam renvoie une page HTML et non du JSON.
        if (!is_array($data)) {
            throw new RuntimeException('Réponse invalide de Steam : la clé API est peut-être refusée.');
        }

        return $data;
    }
}
