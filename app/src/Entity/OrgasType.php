<?php

namespace App\Entity;

enum OrgasType: string
{
    case NOMADS = 'Nomads';
    case TECHERS = 'Tech\'ers';
    case RODOIR = 'Rodoir';
    case NEOCUBA = 'Néo-cuba';
    case PNJ = 'Pnj';
    case MILIEU = 'Milieu';


    public static function getEmail(string $faction): string
    {
        return match ($faction) {
            self::NOMADS->value => 'orgas-nomads@dawn-gn.com',
            self::TECHERS->value => 'orgas-techers@dawn-gn.com',
            self::RODOIR->value => 'orgas-rodoir@dawn-gn.com',
            self::NEOCUBA->value => 'orgas-neocuba@dawn-gn.com',
            self::PNJ->value => 'orgas-pnj@dawn-gn.com',
            self::MILIEU->value => 'orgas-milieu@dawn-gn.com',
            default => 'orgas-nomads@dawn-gn.com',
        };
    }
}
