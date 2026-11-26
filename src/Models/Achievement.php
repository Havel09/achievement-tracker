<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Achievement : un succès d'un jeu Steam.
 * C'est un simple objet qui regroupe les informations d'un succès.
 */
class Achievement
{
    /**
     * @param string $apiName     identifiant technique du succès chez Steam
     * @param string $name        nom affiché
     * @param string $description description ('' pour certains succès cachés)
     * @param string $iconUrl     adresse de l'icône
     * @param bool   $hidden      true si c'est un succès caché
     * @param bool   $unlocked    true si le joueur l'a débloqué
     */
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
