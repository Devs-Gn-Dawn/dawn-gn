<?php

namespace App\Entity;

enum AssetType: string
{
    case OBJECT = 'Object';
    case CAPACITY = 'Capacity';
    case SKILL = 'Skill';
    case GEAR = 'Gear';

    public static function fromString(string $type): AssetType
    {
        return match ($type) {
            'Object' => AssetType::OBJECT,
            'Capacity' => AssetType::CAPACITY,
            'Skill' => AssetType::SKILL,
            'Gear' => AssetType::GEAR,
            default => throw new \InvalidArgumentException('Invalid asset type'),
        };
    }
}
