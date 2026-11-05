<?php

declare(strict_types=1);

namespace App\Models;

// Un succès Steam d'un jeu.
class Achievement
{
    public function __construct(
        public string $apiName,
        public string $name,
        public string $description,
        public string $iconUrl,
        public bool $hidden,
        public bool $unlocked
    ) {
    }
}
