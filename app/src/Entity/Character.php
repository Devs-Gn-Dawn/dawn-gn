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

    #[ORM\Column(type: Types::INTEGER)]
    private int $xp_skill = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $xp_gear = 0;

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

    public function getGainedXp(): int
    {
        $user = $this->getUser();
        $total = 0;
        foreach ($user->getRegistrations() as $registration) {
            if ($registration->getEventType()->getStatus() == EventType::STATUS_CLOSED) {
                $total++;
            }
        }
        return $total;
    }

    public function getAvailableXp(): int
    {
        return $this->getGainedXp() - $this->getSkillsXp() - $this->getGearXp() + 30;
    }

    public function getSkillsXp(): int
    {
        return $this->xp_skill;
    }

    public function getAvailableSkillsXp(): int
    {
        return $this->getSkillsXp() - $this->getSkillsXpUsed();
    }

    public function getGearXp(): int
    {
        return $this->xp_gear;
    }

    public function getAvailableGearXp(): int
    {
        return $this->getGearXp() - $this->getGearXpUsed();
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
            $skills[] = [
                'id' => $skillLearned->getSkill()->getId(),
                'name' => $skillLearned->getSkill()->getLabel(),
                'description' => $skillLearned->getSkill()->getDescription(),
                'required' => $skillLearned->getSkill()->getRequired(),
                'class' => $skillLearned->getSkill()->getClass(),
                'faction' => $skillLearned->getSkill()->getFaction(),
                'cost' => $skillLearned->getCost(),
                'quote' => $skillLearned->getNote()
            ];
        }
        return $skills;
    }

    public function getSpecialSkills(): array
    {
        // TODO: Implémenter la logique pour récupérer les compétences spéciales
        return [];
    }

    public function getEquipment(): array
    {
        $equipment = [];
        foreach ($this->possessions as $possession) {
            $equipment[] = [
                'id' => $possession->getGear()->getId(),
                'name' => $possession->getGear()->getLabel(),
                'description' => $possession->getGear()->getDescription(),
                'cost' => $possession->getCost(),
                'quote' => $possession->getNote()
            ];
        }
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

    public function getAvatar(): string
    {
        return FactionType::getAvatar($this->getFactionType());
    }

    public function addSkill(Skill $skill, ?int $cost = null, ?string $note = null): static
    {
        foreach ($this->skillsLearned as $skillLearned) {
            if ($skillLearned->getSkill() === $skill) {
                throw new \Exception("Compétence déjà apprise");
            }
        }

        if ($this->getPaAvailable() < ($cost ?? $skill->getBaseCost())) {
            throw new \Exception("Points insuffisants");
        }

        $skillLearned = new SkillLearned();
        $skillLearned->setSkill($skill);
        $skillLearned->setCost($cost ?? $skill->getBaseCost());
        $skillLearned->setNote($note);
        $skillLearned->setCharacter($this);

        $this->skillsLearned->add($skillLearned);

        return $this;
    }
}
