<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\CharacterType;
use App\Entity\ValidationType;
use App\Entity\AssetType;

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

    #[ORM\Column(type: Types::INTEGER)]
    private int $xp_skill = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $xp_gear = 0;

    #[ORM\Column(type: Types::STRING, enumType: CharacterType::class)]
    private CharacterType $type = CharacterType::MAIN;

    #[ORM\Column(type: Types::INTEGER, enumType: ValidationType::class)]
    private ValidationType $validationType = ValidationType::NON_VALIDE;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'characters')]
    #[ORM\JoinColumn(name: 'fk_user', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'character', targetEntity: Possession::class)]
    private Collection $possessions;

    #[ORM\OneToMany(mappedBy: 'character', targetEntity: SkillLearned::class, cascade: ['persist'])]
    private Collection $skillsLearned;

    #[ORM\OneToMany(mappedBy: 'character', targetEntity: CharacterAsset::class)]
    private Collection $characterAssets;

    public function __construct()
    {
        $this->possessions = new ArrayCollection();
        $this->skillsLearned = new ArrayCollection();
        $this->characterAssets = new ArrayCollection();
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

    public function setXpSkill(int $xp_skill): static
    {
        $this->xp_skill = $xp_skill;
        return $this;
    }

    public function setXpGear(int $xp_gear): static
    {
        $this->xp_gear = $xp_gear;
        return $this;
    }

    public function getXpSkill(): int
    {
        return $this->xp_skill;
    }

    public function getXpGear(): int
    {
        return $this->xp_gear;
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

    public function getCharacterAssets(): Collection
    {
        return $this->characterAssets;
    }

    public function addCharacterAsset(CharacterAsset $characterAsset): static
    {
        if (!$this->characterAssets->contains($characterAsset)) {
            $this->characterAssets->add($characterAsset);
            $characterAsset->setCharacter($this);
        }
        return $this;
    }

    public function removeCharacterAsset(CharacterAsset $characterAsset): static
    {
        if ($this->characterAssets->removeElement($characterAsset)) {
            $characterAsset->setCharacter(null);
        }
        return $this;
    }

    public function getCharacterAssetsByType(AssetType $type): Collection
    {
        return $this->characterAssets->filter(function (CharacterAsset $characterAsset) use ($type) {
            return $characterAsset->getAsset()->getType() === $type;
        });
    }

    public function isMain(): bool
    {
        return $this->type === CharacterType::MAIN;
    }

    public function isSecondary(): bool
    {
        return $this->type === CharacterType::SECONDARY;
    }

    public function isDraft(): bool
    {
        return $this->type === CharacterType::DRAFT;
    }

    public function getType(): CharacterType
    {
        return $this->type;
    }

    public function setType(CharacterType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getTypeIcon(): string
    {
        return CharacterType::getIcon($this->getType());
    }

    public function getValidationType(): ValidationType
    {
        return $this->validationType;
    }

    public function setValidationType(ValidationType $validationType): static
    {
        $this->validationType = $validationType;
        return $this;
    }

    public function isValidated(): bool
    {
        return $this->validationType === ValidationType::VALIDE;
    }

    public function isRejected(): bool
    {
        return $this->validationType === ValidationType::REJETE;
    }

    public function isInValidation(): bool
    {
        return $this->validationType === ValidationType::EN_COURS;
    }

    public function isNotValidated(): bool
    {
        return $this->validationType === ValidationType::NON_VALIDE;
    }

    public function getGainedXp(): int
    {
        $user = $this->getUser();
        $total = 0;
        foreach ($user->getRegistrations() as $registration) {
            if ($registration->getEventType()->getStatus() == EventType::STATUS_CLOSED) {
                $total += 3;
            }
        }
        return $total;
    }

    public function getAvailableXp(): int
    {
        return $this->getGainedXp() - $this->getXpSkill() - $this->getXpGear() + 30;
    }

    public function getAvailableSkillsXp(): int
    {
        return $this->getXpSkill() - $this->getSkillsXpUsed();
    }

    public function getAvailableGearXp(): int
    {
        return $this->getXpGear() - $this->getGearXpUsed();
    }

    public function getSkillsXpUsed(): int
    {
        $total = 0;
        foreach ($this->skillsLearned as $skillLearned) {
            $total += $skillLearned->getCost();
        }
        return $total;
    }
    public function getGearXpUsed(): int
    {
        $total = 0;
        foreach ($this->possessions as $possession) {
            $total += $possession->getCost();
        }
        return $total;
    }

    public function getSkills(): array
    {
        $skills = [];
        foreach ($this->skillsLearned as $skillLearned) {
            $skill = $skillLearned->getSkill();
            $skills[] = [
                'id' => $skill->getId(),
                'name' => $skill->getLabel(),
                'description' => $skill->getDescription(),
                'required_classes' => $skill->getRequiredClasses(),
                'required_factions' => $skill->getRequiredFactions(),
                'required_skills' => $skill->getRequiredSkills()->toArray(),
                'cost' => $skillLearned->getCost(),
                'quote' => $skillLearned->getNote(),
                'locked' => $skillLearned->isLocked()
            ];
        }
        usort($skills, function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });
        return $skills;
    }

    public function getSpecialSkills(): array
    {
        // get assets with type Capacity from character_asset table
        $assets = $this->characterAssets->filter(function (CharacterAsset $characterAsset) {
            return $characterAsset->getAsset()->getType() === AssetType::CAPACITY;
        });
        $specialSkills = [];
        foreach ($assets as $asset) {
            $specialSkills[] = [
                'id' => $asset->getAsset()->getId(),
                'name' => $asset->getAsset()->getLabel(),
                'description' => $asset->getAsset()->getDescription(),
                'quote' => $asset->getAsset()->getQuote(),
                'note' => $asset->getNote(),
                'noteOrga' => $asset->getNoteOrga(),
                'characterAssetId' => $asset->getId(),
                'locked' => $asset->isLocked()
            ];
        }
        usort($specialSkills, function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });
        return $specialSkills;
    }

    public function getObjects(): array
    {
        $assets = $this->characterAssets->filter(function (CharacterAsset $characterAsset) {
            return $characterAsset->getAsset()->getType() === AssetType::OBJECT;
        });
        $objects = [];
        foreach ($assets as $asset) {
            $objects[] = [
                'id' => $asset->getAsset()->getId(),
                'name' => $asset->getAsset()->getLabel(),
                'description' => $asset->getAsset()->getDescription(),
                'quote' => $asset->getAsset()->getQuote(),
                'note' => $asset->getNote(),
                'noteOrga' => $asset->getNoteOrga(),
                'quantity' => $asset->getQuantity(),
                'characterAssetId' => $asset->getId(),
                'locked' => $asset->isLocked()
            ];
        }
        usort($objects, function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });
        return $objects;
    }
    public function getEquipment($withPossession = true): array
    {
        $equipment = [];
        if ($withPossession) {
            foreach ($this->possessions as $possession) {
                $equipment[] = [
                    'possession' => $possession,
                    'cost' => $possession->getCost(),
                    'quote' => '',
                    'note' => $possession->getNote(),
                    'noteOrga' => $possession->getNoteOrga(),
                    'type' => 'possession',
                    'locked' => $possession->isLocked()
                ];
            }
        }
        usort($equipment, function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });
        return $equipment;
    }

    public function getFactionType(): ?FactionType
    {
        return FactionType::from($this->faction);
    }

    public function getBanner(): string
    {
        return FactionType::getBanner($this->getFactionType());
    }

    public function getBreadCrumbTextColor(): string
    {
        return FactionType::getBreadCrumbTextColor($this->getFactionType());
    }

    public function getAvatar(): string
    {
        return FactionType::getAvatar($this->getFactionType());
    }

    public function addSkill(Skill $skill, ?int $cost = null, ?string $note = null, ?string $noteOrga = null): static
    {
        foreach ($this->skillsLearned as $skillLearned) {
            if ($skillLearned->getSkill() === $skill) {
                throw new \Exception("Compétence déjà apprise");
            }
        }

        if ($this->getAvailableSkillsXp() < ($cost ?? $skill->getBaseCost())) {
            throw new \Exception("Points insuffisants");
        }

        $skillLearned = new SkillLearned();
        $skillLearned->setSkill($skill);
        $skillLearned->setCost($cost ?? $skill->getBaseCost());
        $skillLearned->setNote($note ?? '');
        $skillLearned->setNoteOrga($noteOrga ?? '');
        $skillLearned->setCharacter($this);

        $this->skillsLearned->add($skillLearned);

        return $this;
    }

    public function getPvMax(): int
    {
        $pvMax = 3;
        foreach ($this->skillsLearned as $skillLearned) {
            if (str_contains($skillLearned->getSkill()->getLabel(), 'Constitution')) {
                $pvMax += 1;
            }
        }
        return $pvMax;
    }

    public function getArmor(): int
    {
        $armor = 0;
        foreach ($this->possessions as $possession) {
            if (str_contains($possession->getGear()->getLabel(), 'Kit d\'armure')) {
                $armor = max($armor, $possession->getGear()->getBaseCost() / 2);
            }
        }
        return $armor;
    }

    public function getRadiationsOffset(): int
    {
        $offset = 0;
        foreach ($this->skillsLearned as $skillLearned) {
            if (str_contains($skillLearned->getSkill()->getLabel(), 'Adapté à la radiation')) {
                $offset += 2;
            }
        }
        foreach ($this->characterAssets as $characterAsset) {
            if ($characterAsset->getAsset()->getId() === 137) {
                $offset += 3;
            }
        }
        return $offset;
    }
}
