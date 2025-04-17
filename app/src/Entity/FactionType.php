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

    public static function getBanner(FactionType $faction): string
    {
        return match ($faction) {
            self::NOMADS => 'bandeau_nomads',
            self::TECHERS => 'bandeau_techers',
            self::RODOIR => 'bandeau_rodoir',
            self::NEOCUBA => 'bandeau_neocuba',
        };
    }

    public static function getBreadCrumbTextColor(FactionType $faction): string
    {
        return match ($faction) {
            self::NOMADS => 'text-white',
            self::TECHERS => 'text-white',
            self::RODOIR => 'text-white',
            self::NEOCUBA => 'text-slate-700',
        };
    }

    public static function getAvatar(FactionType $faction): string
    {
        return match ($faction) {
            self::NOMADS => 'avatar_nomads',
            self::TECHERS => 'avatar_techers',
            self::RODOIR => 'avatar_rodoir',
            self::NEOCUBA => 'avatar_neocuba',
        };
    }

    public static function getCharcaterSheetBackground(FactionType $faction): string
    {
        return match ($faction) {
            self::NOMADS => 'fiche_personnage_template-nomads',
            self::TECHERS => 'fiche_personnage_template-techers',
            self::RODOIR => 'fiche_personnage_template-rodoir',
            self::NEOCUBA => 'fiche_personnage_template-neocuba',
        };
    }
}
