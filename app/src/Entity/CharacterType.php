<?php

namespace App\Entity;

enum CharacterType: string
{
    case MAIN = 'Main';
    case SECONDARY = 'Secondary';
    case DRAFT = 'draft';

    public function getLabel(): string
    {
        return match ($this) {
            self::MAIN => 'Principal',
            self::SECONDARY => 'Reroll',
            self::DRAFT => 'Brouillon',
        };
    }

    public static function getChoices(): array
    {
        return array_combine(
            array_map(fn($case) => $case->getLabel(), self::cases()),
            array_map(fn($case) => $case->value, self::cases())
        );
    }

    public static function getIcon(CharacterType $characterType): string
    {
        return match ($characterType) {
            self::MAIN => '<span class="bg-gradient-to-tl from-green-600 to-lime-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Principal</span>',
            self::SECONDARY => '<span class="bg-gradient-to-tl from-blue-600 to-cyan-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Reroll</span>',
            self::DRAFT => '<span class="bg-gradient-to-tl from-gray-600 to-gray-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-black">Brouillon</span>',
        };
    }
}
