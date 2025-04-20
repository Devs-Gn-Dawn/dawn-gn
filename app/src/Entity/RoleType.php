<?php

namespace App\Entity;

class RoleType
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_ORGA = 'ROLE_ORGA';

    public static function getChoices(): array
    {
        return [
            'Utilisateur' => self::ROLE_USER,
            'Administrateur' => self::ROLE_ADMIN,
            'Organisateur' => self::ROLE_ORGA,
        ];
    }

    public static function getLabel(string $role): string
    {
        return match ($role) {
            self::ROLE_USER => 'Utilisateur',
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_ORGA => 'Organisateur',
            default => 'Rôle inconnu',
        };
    }
}
