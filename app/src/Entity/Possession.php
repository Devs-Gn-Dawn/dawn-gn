<?php

namespace App\Entity;

use App\Repository\PossessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PossessionRepository::class)]
class Possession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $cost = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $note = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $note_orga = null;

    #[ORM\ManyToOne(targetEntity: Character::class, inversedBy: 'possessions')]
    #[ORM\JoinColumn(name: 'fk_character', referencedColumnName: 'id', nullable: false)]
    private ?Character $character = null;

    #[ORM\ManyToOne(targetEntity: Gear::class)]
    #[ORM\JoinColumn(name: 'fk_gear', referencedColumnName: 'id', nullable: false)]
    private ?Gear $gear = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCost(): ?int
    {
        return $this->cost;
    }

    public function setCost(int $cost): static
    {
        $this->cost = $cost;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getNoteOrga(): ?string
    {
        return $this->note_orga;
    }

    public function setNoteOrga(string $note_orga): static
    {
        $this->note_orga = $note_orga;

        return $this;
    }

    public function getCharacter(): ?Character
    {
        return $this->character;
    }

    public function setCharacter(?Character $character): static
    {
        $this->character = $character;
        return $this;
    }

    public function getGear(): ?Gear
    {
        return $this->gear;
    }

    public function setGear(?Gear $gear): static
    {
        $this->gear = $gear;
        return $this;
    }
}
