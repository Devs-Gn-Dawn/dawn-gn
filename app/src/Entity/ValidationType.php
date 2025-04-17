<?php

namespace App\Entity;

enum ValidationType: int
{
    case REJETE = -1;
    case NON_VALIDE = 0;
    case EN_COURS = 1;
    case VALIDE = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::REJETE => 'Rejeté',
            self::NON_VALIDE => 'Non validé',
            self::EN_COURS => 'En cours de validation',
            self::VALIDE => 'Validé',
        };
    }
}
