<?php

namespace App\Entity;

enum OrgasType: string
{
    case NOMADS = 'Nomads';
    case TECHERS = 'Tech\'ers';
    case RODOIR = 'Rodoir';
    case NEOCUBA = 'Néo-cuba';
    case PNJ = 'Pnj';


    public static function getEmail(string $faction): string
    {
        return match ($faction) {
            self::NOMADS => 'orgas-nomads@dawn-gn.com',
            self::TECHERS => 'orgas-techers@dawn-gn.com',
            self::RODOIR => 'orgas-rodoir@dawn-gn.com',
            self::NEOCUBA => 'orgas-neocuba@dawn-gn.com',
            self::PNJ => 'orgas-pnj@dawn-gn.com',
            default => 'orgas-nomads@dawn-gn.com',
        };
    }
}
