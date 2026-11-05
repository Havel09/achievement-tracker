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

        // Si la personne a déjà donné un SteamID (17 chiffres), pas besoin d'appeler Steam.
        if (preg_match('/^\d{17}$/', $name) === 1) {
            return $name;
        }

        $data = $this->call('ISteamUser/ResolveVanityURL/v0001/', ['vanityurl' => $name]);

        // success = 1 : profil trouvé, success = 42 : aucun profil avec ce pseudo.
        if (($data['response']['success'] ?? 0) !== 1) {
            throw new RuntimeException("Aucun profil Steam trouvé pour « $name ».");
        }

        return (string) $data['response']['steamid'];
    }

    // récupère la bibliothèque de jeux d'un joueur.
    /** @return Game[] */
    public function getOwnedGames(string $steamId): array
    {
        $data = $this->call('IPlayerService/GetOwnedGames/v0001/', [
            'steamid' => $steamId,
            'include_appinfo' => 1,
        ]);

        // Si le profil est privé, Steam renvoie une réponse vide.
        if (!isset($data['response']['games'])) {
            throw new RuntimeException('Bibliothèque introuvable : le profil est peut-être privé.');
        }

        $games = [];

        foreach ($data['response']['games'] as $g) {
            $appId = (int) $g['appid'];
            $hash = $g['img_icon_url'] ?? '';

            $iconUrl = $hash !== '' ? "https://media.steampowered.com/steamcommunity/public/images/apps/$appId/$hash.jpg" : '';

            $games[] = new Game(
                $appId,
                (string) ($g['name'] ?? 'Jeu inconnu'),
                $iconUrl,
                (int) ($g['playtime_forever'] ?? 0)
            );
        }

        return $games;
    }

    // récupère les succès d'un jeu, avec leur description et s'ils sont débloqués.
    /** @return Achievement[] */
    public function getAchievements(string $steamId, int $appId): array
    {
        // Ce que le joueur a débloqué.
        $player = $this->call('ISteamUserStats/GetPlayerAchievements/v0001/', [
            'steamid' => $steamId,
            'appid' => $appId,
        ]);

        if (!($player['playerstats']['success'] ?? false)) {
            $error = $player['playerstats']['error'] ?? 'Réponse invalide de Steam.';

            // Un jeu sans succès, on renvoie une liste vide.
            if ($error === 'Requested app has no stats') {
                return [];
            }

            throw new RuntimeException($error);
        }

        $unlocked = [];

        foreach ($player['playerstats']['achievements'] ?? [] as $a) {
            $unlocked[$a['apiname']] = (bool) $a['achieved'];
        }

        // La liste complète des succès du jeu : nom, description, icônes.
        $schema = $this->call('ISteamUserStats/GetSchemaForGame/v2/', [
            'appid' => $appId,
            'l' => 'french',
        ]);

        $list = $schema['game']['availableGameStats']['achievements'] ?? [];
        $achievements = [];

        foreach ($list as $s) {
            $isUnlocked = $unlocked[$s['name']] ?? false;

            $achievements[] = new Achievement(
                (string) $s['name'],
                (string) ($s['displayName'] ?? $s['name']),
                (string) ($s['description'] ?? ''),
                (string) ($isUnlocked ? ($s['icon'] ?? '') : ($s['icongray'] ?? '')),
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
