<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Achievement;
use App\Models\Game;
use RuntimeException;

/**
 * SteamService : tous les échanges avec l'API Steam passent par cette classe.
 *
 * Les contrôleurs l'utilisent sans rien savoir de l'API : ils demandent des jeux
 * ou des succès, et reçoivent des objets Game et Achievement.
 * Quand quelque chose ne va pas (pseudo inconnu, profil privé, clé refusée...),
 * une RuntimeException est lancée avec un message lisible, que les contrôleurs affichent.
 */
class SteamService
{
    // Adresse de base de l'API Steam
    private const API_URL = 'https://api.steampowered.com/';

    // La clé API est donnée à la création du service (elle vient du fichier .env)
    public function __construct(private string $apiKey)
    {
    }

    /**
     * Transforme ce que la personne a tapé en SteamID (17 chiffres).
     * Trois cas sont acceptés : le lien du profil, le SteamID, l'URL personnalisée.
     */
    public function resolveVanityUrl(string $name): string
    {
        $name = trim($name);

        // Cas 1 : le lien complet du profil a été collé.
        // Exemples : https://steamcommunity.com/id/pseudo ou .../profiles/76561199...
        // On garde seulement le dernier morceau (pseudo ou SteamID).
        if (str_contains($name, 'steamcommunity.com')) {
            $path = (string) parse_url($name, PHP_URL_PATH);
            $name = basename(rtrim($path, '/'));
        }

        // Cas 2 : c'est déjà un SteamID (17 chiffres), pas besoin d'appeler Steam
        if (preg_match('/^\d{17}$/', $name) === 1) {
            return $name;
        }

        // Cas 3 : c'est une URL personnalisée, on demande son SteamID à Steam
        $data = $this->call('ISteamUser/ResolveVanityURL/v0001/', ['vanityurl' => $name]);
        $response = $data['response'] ?? [];

        // success vaut 1 si le profil existe, 42 si aucun profil n'a ce pseudo
        if (($response['success'] ?? 0) !== 1) {
            throw new RuntimeException("Aucun profil Steam trouvé pour « $name ».");
        }

        return $response['steamid'];
    }

    /**
     * Récupère la bibliothèque d'un joueur : un objet Game par jeu.
     * Les jeux renvoyés n'ont pas encore leurs succès (voir getAchievements).
     *
     * @return Game[]
     */
    public function getOwnedGames(string $steamId): array
    {
        $data = $this->call('IPlayerService/GetOwnedGames/v0001/', [
            'steamid' => $steamId,
            'include_appinfo' => 1, // pour avoir le nom et l'icône de chaque jeu
            'include_played_free_games' => 1, // pour inclure les jeux gratuits
        ]);

        // Si le profil (ou les détails des jeux) est privé, Steam renvoie une réponse vide
        if (!isset($data['response']['games'])) {
            throw new RuntimeException('Bibliothèque introuvable : le profil est peut-être privé.');
        }

        $games = [];

        foreach ($data['response']['games'] as $steamGame) {
            // L'icône se construit avec l'appId et un code que Steam fournit
            $iconUrl = '';

            if (!empty($steamGame['img_icon_url'])) {
                $iconUrl = 'https://media.steampowered.com/steamcommunity/public/images/apps/' . $steamGame['appid'] . '/' . $steamGame['img_icon_url'] . '.jpg';
            }

            $games[] = new Game(
                $steamGame['appid'],
                $steamGame['name'] ?? 'Jeu inconnu',
                $iconUrl,
                $steamGame['playtime_forever'] ?? 0 // temps de jeu en minutes
            );
        }

        return $games;
    }

    /**
     * Récupère les succès d'un jeu pour un joueur : un objet Achievement par succès,
     * avec son nom, sa description et son état (débloqué ou non).
     * Il faut deux appels à Steam : un pour ce que le joueur a débloqué,
     * un autre pour les informations de chaque succès.
     *
     * @return Achievement[]
     */
    public function getAchievements(string $steamId, int $appId): array
    {
        // --- Étape 1 : quels succès le joueur a-t-il débloqués ? ---
        $data = $this->call('ISteamUserStats/GetPlayerAchievements/v0001/', [
            'steamid' => $steamId,
            'appid' => $appId,
        ]);
        $stats = $data['playerstats'] ?? [];

        if (!($stats['success'] ?? false)) {
            $error = $stats['error'] ?? 'Réponse invalide de Steam.';

            // Un jeu sans succès n'est pas une erreur : on renvoie une liste vide
            if ($error === 'Requested app has no stats') {
                return [];
            }

            throw new RuntimeException($error);
        }

        // On range l'état de chaque succès dans un tableau : [nom du succès => true ou false]
        $unlockedByName = [];

        foreach ($stats['achievements'] ?? [] as $playerAchievement) {
            $unlockedByName[$playerAchievement['apiname']] = (bool) $playerAchievement['achieved'];
        }

        // --- Étape 2 : les informations de tous les succès du jeu (en français) ---
        $schema = $this->call('ISteamUserStats/GetSchemaForGame/v2/', [
            'appid' => $appId,
            'l' => 'french',
        ]);

        $achievements = [];

        foreach ($schema['game']['availableGameStats']['achievements'] ?? [] as $info) {
            $isUnlocked = $unlockedByName[$info['name']] ?? false;

            // Icône en couleur si le succès est débloqué, en gris sinon
            $iconUrl = $isUnlocked ? ($info['icon'] ?? '') : ($info['icongray'] ?? '');

            $achievements[] = new Achievement(
                $info['name'],
                $info['displayName'] ?? $info['name'],
                $info['description'] ?? '',
                $iconUrl,
                (bool) ($info['hidden'] ?? false), // Steam envoie 0 ou 1
                $isUnlocked
            );
        }

        return $achievements;
    }

    /**
     * Fait un appel à l'API Steam et renvoie la réponse (JSON) sous forme de tableau.
     * C'est la seule méthode qui parle réellement à Steam.
     */
    private function call(string $endpoint, array $params): array
    {
        // La clé API est ajoutée à chaque appel
        $params['key'] = $this->apiKey;
        $url = self::API_URL . $endpoint . '?' . http_build_query($params);

        // ignore_errors : on veut lire la réponse même quand Steam répond une erreur (400, 403...)
        $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 10]]);

        // Le @ cache l'avertissement de PHP : on gère nous-mêmes l'échec juste en dessous
        $json = @file_get_contents($url, false, $context);

        if ($json === false) {
            throw new RuntimeException("Impossible de contacter l'API Steam.");
        }

        $data = json_decode($json, true);

        // Quand la clé API est refusée, Steam renvoie une page HTML et non du JSON
        if (!is_array($data)) {
            throw new RuntimeException('Réponse invalide de Steam : la clé API est peut-être refusée.');
        }

        return $data;
    }
}
