<?php

namespace App\DTO;

use App\Entity\ClassType;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCharacterDTO
{
    #[Assert\NotBlank(message: "Le nom du personnage est obligatoire")]
    #[Assert\Length(min: 2, max: 50, minMessage: "Le nom doit faire au moins 2 caractères", maxMessage: "Le nom ne peut pas dépasser 50 caractères")]
    private string $characterName;

    #[Assert\NotBlank(message: "La faction est obligatoire")]
    private string $faction;

    #[Assert\NotBlank(message: "La classe est obligatoire")]
    private string $class;

    #[Assert\NotBlank(message: "L'ID de l'utilisateur est obligatoire")]
    #[Assert\Type(type: "integer", message: "L'ID de l'utilisateur doit être un nombre")]
    private int $userId;

    #[Assert\Length(max: 1000000, maxMessage: "Le background ne peut pas dépasser 1 000 000 caractères")]
    private ?string $background = null;

    public function getCharacterName(): string
    {
        return $this->characterName;
    }

    public function setCharacterName(string $characterName): self
    {
        $this->characterName = $characterName;
        return $this;
    }

    public function getFaction(): string
    {
        return $this->faction;
    }

    public function setFaction(string $faction): self
    {
        $this->faction = $faction;
        return $this;
    }

    public function getClass(): ClassType
    {
        return ClassType::from($this->class);
    }

    public function setClass(ClassType $class): self
    {
        $this->class = $class->value;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(?string $background): self
    {
        $this->background = $background;
        return $this;
    }
}
