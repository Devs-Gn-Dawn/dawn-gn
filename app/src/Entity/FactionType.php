<?php

namespace App\Entity;

enum FactionType: string
{
    case NOMADS = 'Nomads';
    case TECHERS = 'Tech\'ers';
    case RODOIR = 'Rodoir';
    case NEOCUBA = 'Néo-cuba';

    public function getLabel(): string
    {
        return match ($this) {
            self::NOMADS => 'Nomads',
            self::TECHERS => 'Tech\'ers',
            self::RODOIR => 'Rodoir',
            self::NEOCUBA => 'Néo-cuba',
        };
    }

    public static function getChoices(): array
    {
        return array_combine(
            array_map(fn($case) => $case->getLabel(), self::cases()),
            array_map(fn($case) => $case->value, self::cases())
        );
    }
}
