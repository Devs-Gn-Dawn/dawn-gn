<?php

namespace App\Entity;

enum AssetType: string
{
    case OBJECT = 'Object';
    case CAPACITY = 'Capacity';
    case SKILL = 'Skill';
    case GEAR = 'Gear';
}
