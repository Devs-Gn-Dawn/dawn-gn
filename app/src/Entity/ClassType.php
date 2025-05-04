<?php

namespace App\Entity;

enum ClassType: string
{
    case RUNNERS = 'Runners';
    case RAIDERS = 'Raiders';
    case SHAMANS = 'Shamans';
    case RADIATIONISTE = 'Radiationiste';
    case LEGIONNAIRE = 'Légionnaire';
    case FACONNEUR = 'Façonneur';
    case MECANISTE = 'Mécaniste';
    case PILOTE_HOMME_RAT = 'Pilote (Homme-rat)';
    case POUCE_ARTISANS = 'Le pouce (artisans)';
    case INDEX_SCIENTIFIQUES = 'L\'index (scientifiques)';
    case MAJEUR_SOLDATS = 'Le majeur (soldats)';
    case ANNULAIRE_COLLECTEURS = 'L\'annulaire (collecteurs)';
    case AURICULAIRE_POLITICIENS = 'L\'auriculaire (Politiciens)';
    case NEOCUBA = 'Néo-cubanais';

    public function getLabel(): string
    {
        return match ($this) {
            self::RUNNERS => 'Runner',
            self::RAIDERS => 'Raider',
            self::SHAMANS => 'Shaman',
            self::RADIATIONISTE => 'Radiationiste',
            self::LEGIONNAIRE => 'Légionnaire',
            self::FACONNEUR => 'Façonneur',
            self::MECANISTE => 'Mécaniste',
            self::PILOTE_HOMME_RAT => 'Pilote (Homme-rat)',
            self::POUCE_ARTISANS => 'Le pouce (artisans)',
            self::INDEX_SCIENTIFIQUES => 'L\'index (scientifiques)',
            self::MAJEUR_SOLDATS => 'Le majeur (soldats)',
            self::ANNULAIRE_COLLECTEURS => 'L\'annulaire (collecteurs)',
            self::AURICULAIRE_POLITICIENS => 'L\'auriculaire (Politiciens)',
            self::NEOCUBA => 'Néo-cubanais',
        };
    }

    public function getRequiredFaction(): ?FactionType
    {
        return match ($this) {
            self::RUNNERS => FactionType::NOMADS,
            self::RAIDERS => FactionType::NOMADS,
            self::SHAMANS => FactionType::NOMADS,
            self::RADIATIONISTE => FactionType::NOMADS,
            self::LEGIONNAIRE => FactionType::TECHERS,
            self::FACONNEUR => FactionType::TECHERS,
            self::MECANISTE => FactionType::TECHERS,
            self::PILOTE_HOMME_RAT => FactionType::TECHERS,
            self::POUCE_ARTISANS => FactionType::RODOIR,
            self::INDEX_SCIENTIFIQUES => FactionType::RODOIR,
            self::MAJEUR_SOLDATS => FactionType::RODOIR,
            self::ANNULAIRE_COLLECTEURS => FactionType::RODOIR,
            self::AURICULAIRE_POLITICIENS => FactionType::RODOIR,
            self::NEOCUBA => FactionType::NEOCUBA,
        };
    }

    public static function getChoices(): array
    {
        return array_combine(
            array_map(fn($case) => $case->getLabel(), self::cases()),
            array_map(fn($case) => $case->value, self::cases())
        );
    }

    public static function getChoicesForFaction(FactionType $faction): array
    {
        return array_combine(
            array_map(fn($case) => $case->getLabel(), array_filter(self::cases(), fn($case) => $case->getRequiredFaction() === $faction)),
            array_map(fn($case) => $case->value, array_filter(self::cases(), fn($case) => $case->getRequiredFaction() === $faction))
        );
    }
}
