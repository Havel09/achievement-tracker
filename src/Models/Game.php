<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Game : un jeu de la bibliothèque Steam.
 * C'est un simple objet qui regroupe les informations d'un jeu.
 */
class Game
{
    /**
     * @param int           $appId           numéro du jeu sur Steam
     * @param string        $name            nom du jeu
     * @param string        $iconUrl         adresse de l'icône ('' s'il n'y en a pas)
     * @param int           $playtimeMinutes temps de jeu du joueur, en minutes
     * @param Achievement[] $achievements    succès du jeu (vide tant qu'on ne les a pas chargés)
     */
    public function __construct(
        public int $appId,
        public string $name,
        public string $iconUrl,
        public int $playtimeMinutes,
        public array $achievements = []
    ) {
    }

    /**
     * Lien vers le wiki du jeu : une recherche faite avec le nom du jeu.
     */
    public function getWikiUrl(): string
    {
        // urlencode transforme le texte pour qu'il passe dans une adresse (espaces, accents...)
        return 'https://www.google.com/search?q=' . urlencode($this->name . ' wiki');
    }

    /**
     * Progression du joueur en pourcentage : succès débloqués / nombre total de succès.
     */
    public function getProgress(): float
    {
        $total = count($this->achievements);

        // Sans succès, on évite la division par zéro
        if ($total === 0) {
            return 0.0;
        }

        // On compte les succès débloqués
        $unlocked = 0;

        foreach ($this->achievements as $achievement) {
            if ($achievement->unlocked) {
                $unlocked++;
            }
        }

        // Arrondi à une décimale (exemple : 42.5)
        return round($unlocked / $total * 100, 1);
    }
}
