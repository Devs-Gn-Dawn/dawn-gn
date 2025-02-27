<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: '`character`')]
class Character
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 127)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $background = null;

    #[ORM\Column(length: 31)]
    private ?string $class = null;

    #[ORM\Column(length: 31)]
    private ?string $faction = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $note_orga = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isMain = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isValidated = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'characters')]
    #[ORM\JoinColumn(name: 'fk_user', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'character', targetEntity: Possession::class)]
    private Collection $possessions;

    #[ORM\OneToMany(mappedBy: 'character', targetEntity: SkillLearned::class)]
    private Collection $skillsLearned;

    public function __construct()
    {
        $this->possessions = new ArrayCollection();
        $this->skillsLearned = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(string $background): static
    {
        $this->background = $background;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getFaction(): ?string
    {
        return $this->faction;
    }

    public function setFaction(string $faction): static
    {
        $this->faction = $faction;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, Possession>
     */
    public function getPossessions(): Collection
    {
        return $this->possessions;
    }

    /**
     * @return Collection<int, SkillLearned>
     */
    public function getSkillsLearned(): Collection
    {
        return $this->skillsLearned;
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): static
    {
        $this->isMain = $isMain;
        return $this;
    }

    public function isValidated(): bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): static
    {
        $this->isValidated = $isValidated;
        return $this;
    }
}
