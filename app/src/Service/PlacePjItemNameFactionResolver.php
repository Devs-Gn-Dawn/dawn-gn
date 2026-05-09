<?php

namespace App\Service;

use App\Entity\FactionType;

/**
 * Déduit la faction joueur depuis le libellé billet HelloAsso (tarifs « Place PJ … »).
 */
class PlacePjItemNameFactionResolver
{
    public function resolve(?string $itemName): ?FactionType
    {
        if ($itemName === null) {
            return null;
        }

        $trimmed = trim($itemName);
        if ($trimmed === '') {
            return null;
        }

        $n = mb_strtolower($trimmed, 'UTF-8');
        if (mb_strpos($n, 'place pj') !== 0) {
            return null;
        }

        if (
            str_contains($n, 'néo-cuba')
            || str_contains($n, 'neo cuba')
            || str_contains($n, 'neocuba')
        ) {
            return FactionType::NEOCUBA;
        }

        if (str_contains($n, 'tech\'ers') || str_contains($n, 'techers')) {
            return FactionType::TECHERS;
        }

        if (str_contains($n, 'nomads')) {
            return FactionType::NOMADS;
        }

        if (str_contains($n, 'rodoir')) {
            return FactionType::RODOIR;
        }

        return null;
    }
}
