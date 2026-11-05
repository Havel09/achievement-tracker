<?php

declare(strict_types=1);

namespace App\Models;

// Un jeu de la bibliothèque Steam.
class Game
{
    /**
     * @param Achievement[] $achievements
     */
    public function __construct(
        public int $appId,
        public string $name,
        public string $iconUrl,
        public int $playtimeMinutes,
        public array $achievements = []
    ) {
    }

    // Lien vers le wiki du jeu (recherche faite avec le nom du jeu).
    public function getWikiUrl(): string
    {
        return 'https://www.google.com/search?q=' . urlencode($this->name . ' wiki');
    }

    // Progression en % : succès débloqués / nombre total de succès.
    public function getProgress(): float
    {
        $total = count($this->achievements);

        if ($total === 0) {
            return 0.0;
        }

        $unlocked = 0;
        foreach ($this->achievements as $achievement) {
            if ($achievement->unlocked) {
                $unlocked++;
            }
        }

        return round($unlocked / $total * 100, 1);
    }
}
