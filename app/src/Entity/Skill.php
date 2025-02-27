<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 31)]
    private ?string $label = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $base_cost = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $short = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $required_classes = [];

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $required_factions = [];

    #[ORM\Column]
    private ?bool $visibility = null;

    #[ORM\ManyToMany(targetEntity: self::class)]
    #[ORM\JoinTable(name: 'skill_requirements')]
    #[ORM\JoinColumn(name: 'skill_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'required_skill_id', referencedColumnName: 'id')]
    private Collection $requiredSkills;

    public function __construct()
    {
        $this->requiredSkills = new ArrayCollection();
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

    public function getShort(): ?string
    {
        return $this->short;
    }

    public function setShort(string $short): static
    {
        $this->short = $short;

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

    public function getRequiredClasses(): array
    {
        return $this->required_classes;
    }

    public function setRequiredClasses(array $required_classes): static
    {
        $this->required_classes = $required_classes;

        return $this;
    }

    public function getRequiredFactions(): array
    {
        return $this->required_factions;
    }

    public function setRequiredFactions(array $required_factions): static
    {
        $this->required_factions = $required_factions;

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

    /**
     * @return Collection<int, Skill>
     */
    public function getRequiredSkills(): Collection
    {
        return $this->requiredSkills;
    }

    public function addRequiredSkill(self $skill): static
    {
        if (!$this->requiredSkills->contains($skill)) {
            $this->requiredSkills->add($skill);
        }

        return $this;
    }

    public function removeRequiredSkill(self $skill): static
    {
        $this->requiredSkills->removeElement($skill);

        return $this;
    }
}
