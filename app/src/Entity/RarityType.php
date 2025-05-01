<?php

namespace App\Entity;

enum RarityType: string
{
    case ABONDANT = 'Abondant';
    case COMMUN = 'Commun';
    case PEU_COMMUN = 'Peu Commun';
    case RARE = 'Rare';
    case TRES_RARE = 'Très Rare';
    case EXTREMEMENT_RARE = 'Extrêmement Rare';
    case UNIQUE = 'Unique';
    case SECRET = 'Secret';

    public static function getChoices(): array
    {
        return array_combine(
            array_map(fn(RarityType $type) => $type->value, self::cases()),
            array_map(fn(RarityType $type) => $type->value, self::cases())
        );
    }
}
