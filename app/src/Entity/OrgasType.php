<?php

namespace App\Entity;

enum OrgasType: string
{
    case NOMADS = 'Nomads';
    case TECHERS = 'Tech\'ers';
    case RODOIR = 'Rodoir';
    case NEOCUBA = 'Néo-cuba';
    case PNJ = 'Pnj';
    case GENERIQUE = 'Générique';

    
    public function getEmail(): string
    {
        return match ($this) {
            self::NOMADS => 'orgas-nomads@dawn-gn.com',
            self::TECHERS => 'orgas-techers@dawn-gn.com',
            self::RODOIR => 'orgas-rodoir@dawn-gn.com',
            self::NEOCUBA => 'orgas-neocuba@dawn-gn.com',
            self::PNJ => 'orgas-pnj@dawn-gn.com',
            self::GENERIQUE => 'orgas-nomads@dawn-gn.com',
        };
    }
    
}
