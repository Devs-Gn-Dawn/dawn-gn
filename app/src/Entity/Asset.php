<?php

namespace App\Entity;

use App\Repository\AssetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: AssetRepository::class)]
class Asset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: AssetType::class)]
    private ?AssetType $type = null;

    #[ORM\Column]
    private ?bool $is_catalog = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $base_cost = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $short = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $quote = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $required_classes = [];

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $required_factions = [];

    #[ORM\ManyToOne(targetEntity: Skill::class)]
    private ?Skill $required_skill = null;

    #[ORM\Column]
    private ?bool $visibility = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $base_note = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $base_note_orga = null;

    #[ORM\Column(type: 'string', enumType: RarityType::class)]
    private ?RarityType $rarity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?AssetType
    {
        return $this->type;
    }

    public function setType(AssetType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function isIsCatalog(): ?bool
    {
        return $this->is_catalog;
    }

    public function setIsCatalog(bool $is_catalog): static
    {
        $this->is_catalog = $is_catalog;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getBaseCost(): ?int
    {
        return $this->base_cost;
    }

    public function setBaseCost(int $base_cost): static
    {
        $this->base_cost = $base_cost;
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

    public function getShort(): ?string
    {
        return $this->short;
    }

    public function setShort(string $short): static
    {
        $this->short = $short;
        return $this;
    }

    public function getQuote(): ?string
    {
        return $this->quote;
    }

    public function setQuote(string $quote): static
    {
        $this->quote = $quote;
        return $this;
    }

    public function getRarity(): ?RarityType
    {
        return $this->rarity;
    }

    public function setRarity(RarityType $rarity): static
    {
        $this->rarity = $rarity;
        return $this;
    }

    public function getRequiredClasses(): array
    {
        return array_values(array_filter($this->required_classes, fn($value) => trim($value) !== ''));
    }

    public function setRequiredClasses(array $required_classes): static
    {
        $this->required_classes = $required_classes;
        return $this;
    }

    public function getRequiredFactions(): array
    {
        return array_values(array_filter($this->required_factions, fn($value) => trim($value) !== ''));
    }

    public function setRequiredFactions(array $required_factions): static
    {
        $this->required_factions = $required_factions;
        return $this;
    }

    public function getRequiredSkill(): ?Skill
    {
        return $this->required_skill;
    }

    public function setRequiredSkill(?Skill $required_skill): static
    {
        $this->required_skill = $required_skill;
        return $this;
    }

    public function isVisibility(): ?bool
    {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): static
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getBaseNote(): ?string
    {
        return $this->base_note;
    }

    public function setBaseNote(?string $base_note): static
    {
        $this->base_note = $base_note;
        return $this;
    }

    public function getBaseNoteOrga(): ?string
    {
        return $this->base_note_orga;
    }

    public function setBaseNoteOrga(?string $base_note_orga): static
    {
        $this->base_note_orga = $base_note_orga;
        return $this;
    }
}
