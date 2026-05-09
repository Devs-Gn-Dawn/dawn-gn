<?php

namespace App\Service;

use App\DTO\CreateCharacterDTO;
use App\Entity\Character;
use App\Entity\User;
use App\Entity\Skill;
use App\Entity\Gear;
use App\Entity\Asset;
use App\Entity\CharacterAsset;
use App\Entity\SkillLearned;
use App\Entity\Possession;
use App\Entity\FactionType;
use App\Entity\ClassType;
use App\Entity\CharacterType;
use App\Entity\ValidationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SkillRepository;
use Psr\Log\LoggerInterface;

class CharacterService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SkillRepository $skillRepository,
        private LoggerInterface $logger
    ) {}

    /**
     * Crée un nouveau personnage
     */
    public function createCharacter(CreateCharacterDTO $dto, User $user, bool $isOrga = false): Character
    {
        // Validation des valeurs de faction
        if (!in_array($dto->getFaction(), FactionType::getChoices())) {
            throw new \Exception('Faction invalide');
        }

        $character = new Character();
        $character->setUser($user);
        $character->setName(htmlspecialchars($dto->getCharacterName(), ENT_QUOTES, 'UTF-8'));
        $character->setFaction($dto->getFaction());
        $character->setClass($dto->getClass());
        $character->setBackground($dto->getBackground() ? htmlspecialchars($dto->getBackground(), ENT_QUOTES, 'UTF-8') : '');
        $character->setDescription('');
        $character->setNoteOrga('');
        $character->setXpSkill(20);
        $character->setXpGear(10);
        $character->setType(CharacterType::DRAFT);
        $character->setValidationType(ValidationType::NON_VALIDE);

        $this->entityManager->persist($character);
        $this->entityManager->flush();

        return $character;
    }

    /**
     * Vérifie que l'utilisateur a accès au personnage
     */
    public function checkCharacterAccess(Character $character, User $user): void
    {
        if ($character->getUser() !== $user && !$user->isOrga()) {
            throw new \Exception('Vous n\'êtes pas autorisé à modifier ce personnage.');
        }
    }

    /**
     * Vérifie que le personnage peut être modifié (pas déjà validé/rejeté/en validation)
     */
    public function checkCharacterValidation(Character $character): void
    {
        if ($character->isValidated()) {
            throw new \Exception('Ce personnage est déjà validé.');
        }
        if ($character->isRejected()) {
            throw new \Exception('Ce personnage a été rejeté.');
        }
        if ($character->isInValidation()) {
            throw new \Exception('Ce personnage est en cours de validation.');
        }
    }

    /**
     * Ajoute une compétence au personnage
     */
    public function addSkill(Character $character, Skill $skill, ?int $cost = null, ?string $note = null, ?string $noteOrga = null, bool $isOrga = false): void
    {
        if (!$isOrga) {
            $this->checkCharacterValidation($character);
        }

        if ($this->skillRepository->isSkillAvailableForCharacter($skill, $character, $isOrga ? $cost : null)) {
            $character->addSkill($skill, $isOrga ? $cost : null, $isOrga ? $note : null, $isOrga ? $noteOrga : null);
            $this->entityManager->flush();
        } else {
            throw new \Exception('Cette compétence n\'est pas disponible pour ce personnage');
        }
    }

    /**
     * Modifie une compétence du personnage
     */
    public function editSkill(Character $character, Skill $skill, int $cost, string $note, string $noteOrga): void
    {
        $skillLearned = $this->entityManager->getRepository(SkillLearned::class)->findOneBy([
            'character' => $character,
            'skill' => $skill
        ]);

        if (!$skillLearned) {
            throw new \Exception('Compétence non trouvée');
        }

        $skillLearned->setCost($cost);
        $skillLearned->setNote($note);
        $skillLearned->setNoteOrga($noteOrga);

        $this->entityManager->flush();
    }

    /**
     * Supprime une compétence du personnage
     */
    public function deleteSkill(Character $character, int $skillId, bool $isOrga = false): void
    {
        $skillLearned = $character->getSkillsLearned()->filter(
            fn($skillLearned) => $skillLearned->getSkill()->getId() === $skillId
        )->first();

        if (!$skillLearned) {
            throw new \Exception('Cette compétence n\'est pas apprise par ce personnage');
        }

        if ($skillLearned->isLocked() && !$isOrga) {
            throw new \Exception('Cette compétence est verrouillée et ne peut pas être supprimée');
        }

        $this->entityManager->remove($skillLearned);
        $this->entityManager->flush();
    }

    /**
     * Ajoute un équipement au personnage
     */
    public function addGear(Character $character, Gear $gear, ?int $cost = null, ?string $note = null, ?string $noteOrga = null, bool $isOrga = false): void
    {
        if (!$isOrga) {
            $this->checkCharacterValidation($character);
        }

        $character->addGear($gear, $isOrga ? $cost : null, $isOrga ? $note : null, $isOrga ? $noteOrga : null);
        $this->entityManager->flush();
    }

    /**
     * Modifie un équipement du personnage
     */
    public function editGear(Character $character, int $possessionId, int $cost, string $note, string $noteOrga): void
    {
        $possession = $this->entityManager->getRepository(Possession::class)->find($possessionId);
        if (!$possession) {
            throw new \Exception('Équipement non trouvé');
        }

        $possession->setCost($cost);
        $possession->setNote($note);
        $possession->setNoteOrga($noteOrga);

        $this->entityManager->flush();
    }

    /**
     * Supprime un équipement du personnage
     */
    public function deleteGear(Character $character, int $possessionId, bool $isOrga = false): void
    {
        $possession = $this->entityManager->getRepository(Possession::class)->find($possessionId);
        if (!$possession) {
            throw new \Exception('Équipement non trouvé');
        }

        if ($possession->isLocked() && !$isOrga) {
            throw new \Exception('Cet équipement est verrouillé et ne peut pas être supprimé');
        }

        $this->entityManager->remove($possession);
        $this->entityManager->flush();
    }

    /**
     * Ajoute un asset au personnage
     */
    public function addAsset(Character $character, array $data, bool $isOrga = false): CharacterAsset
    {
        if (empty($data['assetId']) && (!$data['isCustom'] ?? false || empty($data['assetName'] ?? null))) {
            throw new \Exception('Tous les champs sont obligatoires');
        }

        if ($data['isCustom'] ?? false) {
            // Créer un asset personnalisé
            $customAsset = new Asset();
            $customAsset->setLabel($data['assetName']);
            $customAsset->setIsCatalog(false);
            $customAsset->setBaseCost(0);
            $customAsset->setQuote('');
            $customAsset->setRequiredClasses(['']);
            $customAsset->setRequiredFactions(['']);
            $customAsset->setVisibility(true);
            $customAsset->setBaseNote($data['assetNote'] ?? '');
            $customAsset->setBaseNoteOrga($data['assetNoteOrga'] ?? '');
            $customAsset->setDescription($data['assetDescription'] ?? '');
            $customAsset->setType(\App\Entity\AssetType::fromString($data['assetType'] ?? 'object'));
            $customAsset->setShort($data['assetShort'] ?? '');
            $customAsset->setRarity(\App\Entity\RarityType::UNIQUE);

            $this->entityManager->persist($customAsset);
            $this->entityManager->flush();

            $asset = $customAsset;
        } else {
            $asset = $this->entityManager->getRepository(Asset::class)->find($data['assetId']);
            if (!$asset) {
                throw new \Exception('Asset non trouvé');
            }
        }

        $characterAsset = new CharacterAsset();
        $characterAsset->setCharacter($character);
        $characterAsset->setAsset($asset);
        $characterAsset->setCost(0);
        $characterAsset->setQuantity($data['assetQuantity'] ?? 1);
        $characterAsset->setNote($data['assetNote'] ?? '');
        $characterAsset->setNoteOrga($data['assetNoteOrga'] ?? '');

        $this->entityManager->persist($characterAsset);
        $this->entityManager->flush();

        return $characterAsset;
    }

    /**
     * Modifie un asset du personnage
     */
    public function editAsset(Character $character, int $characterAssetId, array $data): void
    {
        if (empty($data['characterAssetId'])) {
            throw new \Exception('Tous les champs sont obligatoires');
        }

        $characterAsset = $this->entityManager->getRepository(CharacterAsset::class)->find($characterAssetId);
        if (!$characterAsset) {
            throw new \Exception('Asset non trouvé');
        }

        if (!$characterAsset->getAsset()->isIsCatalog()) {
            $asset = $characterAsset->getAsset();
            $asset->setLabel($data['assetName'] ?? '');
            $asset->setDescription($data['assetDescription'] ?? '');
            $asset->setShort($data['assetShort'] ?? '');
            $this->entityManager->flush();
        }

        $characterAsset->setNote($data['note'] ?? '');
        $characterAsset->setNoteOrga($data['noteOrga'] ?? '');

        $asset = $characterAsset->getAsset();
        if ($asset->getType() == \App\Entity\AssetType::OBJECT) {
            $characterAsset->setQuantity($data['assetQuantity'] ?? 1);
        }

        $this->entityManager->flush();
    }

    /**
     * Supprime un asset du personnage
     */
    public function deleteAsset(Character $character, int $characterAssetId): void
    {
        $characterAsset = $this->entityManager->getRepository(CharacterAsset::class)->find($characterAssetId);
        if (!$characterAsset) {
            throw new \Exception('Asset non trouvé');
        }

        // Vérifier que l'asset appartient au personnage
        if ($characterAsset->getCharacter() !== $character) {
            throw new \Exception('Asset non trouvé pour ce personnage');
        }

        $this->entityManager->remove($characterAsset);
        $this->entityManager->flush();
    }

    /**
     * Ajoute de l'XP Skills au personnage
     */
    public function addSkillXp(Character $character, int $xp): void
    {
        if ($character->getValidationType() === ValidationType::REJETE) {
            throw new \Exception('Ce personnage a été rejeté.');
        }

        if (!is_numeric($xp) || $xp <= 0) {
            throw new \Exception('Valeur d\'XP invalide');
        }

        $availableXp = $character->getAvailableXp();
        if ($xp > $availableXp) {
            throw new \Exception('Points d\'XP insuffisants');
        }

        $character->setXpSkill($character->getXpSkill() + $xp);
        $this->entityManager->flush();
    }

    /**
     * Retire de l'XP Skills au personnage
     */
    public function removeSkillXp(Character $character, int $xp): void
    {
        if ($character->getValidationType() === ValidationType::REJETE) {
            throw new \Exception('Ce personnage a été rejeté.');
        }

        if (!is_numeric($xp) || $xp <= 0) {
            throw new \Exception('Valeur d\'XP invalide');
        }

        $currentSkillXp = $character->getXpSkill();
        $usedSkillXp = $character->getSkillsXpUsed();
        $availableXp = min($currentSkillXp - 20, $currentSkillXp - $usedSkillXp);

        if ($xp > $availableXp) {
            throw new \Exception('Points d\'XP insuffisants');
        }

        $character->setXpSkill($character->getXpSkill() - $xp);
        $this->entityManager->flush();
    }

    /**
     * Ajoute de l'XP Gear au personnage
     */
    public function addGearXp(Character $character, int $xp): void
    {
        $this->checkCharacterValidation($character);

        if (!is_numeric($xp) || $xp <= 0) {
            throw new \Exception('Valeur d\'XP invalide');
        }

        $availableXp = $character->getAvailableXp();
        if ($xp > $availableXp) {
            throw new \Exception('Points d\'XP insuffisants');
        }

        $character->setXpGear($character->getXpGear() + $xp);
        $this->entityManager->flush();
    }

    /**
     * Retire de l'XP Gear au personnage
     */
    public function removeGearXp(Character $character, int $xp): void
    {
        if ($character->getValidationType() === ValidationType::REJETE) {
            throw new \Exception('Ce personnage a été rejeté.');
        }

        if (!is_numeric($xp) || $xp <= 0) {
            throw new \Exception('Valeur d\'XP invalide');
        }

        $currentGearXp = $character->getXpGear();
        $usedGearXp = $character->getGearXpUsed();
        $availableXp = min($currentGearXp - 10, $currentGearXp - $usedGearXp);

        if ($xp > $availableXp) {
            throw new \Exception('Points d\'XP insuffisants');
        }

        $character->setXpGear($character->getXpGear() - $xp);
        $this->entityManager->flush();
    }

    /**
     * Valide un personnage
     */
    public function validateCharacter(Character $character): void
    {
        $character->setValidationType(ValidationType::VALIDE);
        $this->entityManager->flush();
    }

    /**
     * Rejette un personnage
     */
    public function rejectCharacter(Character $character): void
    {
        $character->setValidationType(ValidationType::NON_VALIDE);
        $this->entityManager->flush();
    }

    /**
     * Changement de type (Principal / Reroll / Brouillon) par orga ou admin.
     * Ne applique pas les contraintes du flux joueur (validation, etc.).
     * Si la cible devient Principal ou Reroll, les autres fiches du même joueur
     * occupant ce slot passent automatiquement en brouillon.
     *
     * @return array{previousType: string, newType: string, demotedCharacters: list<array{id: int, name: string, previousType: string}>}
     */
    public function changeCharacterTypeForStaff(Character $character, CharacterType $newType, User $actor): array
    {
        if (!$actor->isOrga() && !$actor->isAdmin()) {
            throw new \Exception('Cette action est réservée aux organisateurs et aux administrateurs.');
        }

        $previousType = $character->getType();
        if ($previousType === $newType) {
            return [
                'previousType' => $previousType->value,
                'newType' => $newType->value,
                'demotedCharacters' => [],
            ];
        }

        $owner = $character->getUser();
        $demoted = [];

        if ($newType === CharacterType::MAIN) {
            foreach ($owner->getCharacters() as $other) {
                if ($other === $character) {
                    continue;
                }
                if ($other->isMain()) {
                    $other->setType(CharacterType::DRAFT);
                    $demoted[] = [
                        'id' => $other->getId(),
                        'name' => (string) $other->getName(),
                        'previousType' => CharacterType::MAIN->value,
                    ];
                }
            }
        } elseif ($newType === CharacterType::SECONDARY) {
            foreach ($owner->getCharacters() as $other) {
                if ($other === $character) {
                    continue;
                }
                if ($other->isSecondary()) {
                    $other->setType(CharacterType::DRAFT);
                    $demoted[] = [
                        'id' => $other->getId(),
                        'name' => (string) $other->getName(),
                        'previousType' => CharacterType::SECONDARY->value,
                    ];
                }
            }
        }

        $character->setType($newType);
        $this->entityManager->flush();

        $this->logger->info('character_type_changed_by_staff', [
            'character_id' => $character->getId(),
            'owner_user_id' => $owner->getId(),
            'actor_id' => $actor->getId(),
            'actor_email' => $actor->getUserIdentifier(),
            'from' => $previousType->value,
            'to' => $newType->value,
            'demoted_character_ids' => array_column($demoted, 'id'),
        ]);

        return [
            'previousType' => $previousType->value,
            'newType' => $newType->value,
            'demotedCharacters' => $demoted,
        ];
    }

    public function assertClassBelongsToFaction(ClassType $class, FactionType $faction): void
    {
        if ($class->getRequiredFaction() !== $faction) {
            throw new \Exception(sprintf(
                'La classe « %s » n\'appartient pas à la faction « %s ».',
                $class->getLabel(),
                $faction->getLabel()
            ));
        }
    }

    /**
     * @return list<SkillLearned>
     */
    public function getSkillLearnedToRemoveForFactionClassChange(Character $character, FactionType $newFaction, ClassType $newClass): array
    {
        $this->assertClassBelongsToFaction($newClass, $newFaction);

        $learned = $character->getSkillsLearned()->toArray();
        $keep = array_values(array_filter(
            $learned,
            fn (SkillLearned $sl) => $this->skillRepository->skillMatchesFactionAndClass(
                $sl->getSkill(),
                $newFaction->value,
                $newClass
            )
        ));

        $changed = true;
        while ($changed) {
            $changed = false;
            $skillIdKept = [];
            foreach ($keep as $sl) {
                $skillIdKept[$sl->getSkill()->getId()] = true;
            }
            $nextKeep = [];
            foreach ($keep as $sl) {
                $ok = true;
                foreach ($sl->getSkill()->getRequiredSkills() as $req) {
                    if (empty($skillIdKept[$req->getId()])) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok) {
                    $nextKeep[] = $sl;
                } else {
                    $changed = true;
                }
            }
            $keep = $nextKeep;
        }

        $keepSlIds = array_fill_keys(array_map(fn (SkillLearned $sl) => $sl->getId(), $keep), true);

        return array_values(array_filter(
            $learned,
            fn (SkillLearned $sl) => !isset($keepSlIds[$sl->getId()])
        ));
    }

    /**
     * @param list<SkillLearned> $removed
     *
     * @return list<array{id: int|null, skillId: int|null, label: string|null, cost: int|null}>
     */
    public function serializeRemovedSkillLearned(array $removed): array
    {
        $out = [];
        foreach ($removed as $sl) {
            if (!$sl instanceof SkillLearned) {
                continue;
            }
            $out[] = [
                'id' => $sl->getId(),
                'skillId' => $sl->getSkill()->getId(),
                'label' => $sl->getSkill()->getLabel(),
                'cost' => $sl->getCost(),
            ];
        }

        return $out;
    }

    /**
     * @return array{skillsToRemove: list<array{id: int|null, skillId: int|null, label: string|null, cost: int|null}>, removedCount: int, totalCostRemoved: int}
     */
    public function previewFactionClassChangeForStaff(
        Character $character,
        FactionType $newFaction,
        ClassType $newClass,
        User $actor
    ): array {
        if (!$actor->isOrga() && !$actor->isAdmin()) {
            throw new \Exception('Cette action est réservée aux organisateurs et aux administrateurs.');
        }

        $toRemove = $this->getSkillLearnedToRemoveForFactionClassChange($character, $newFaction, $newClass);
        $serialized = $this->serializeRemovedSkillLearned($toRemove);
        $totalCost = 0;
        foreach ($toRemove as $sl) {
            $totalCost += (int) $sl->getCost();
        }

        return [
            'skillsToRemove' => $serialized,
            'removedCount' => \count($toRemove),
            'totalCostRemoved' => $totalCost,
        ];
    }

    /**
     * @return array{
     *   previousFaction: string,
     *   previousClass: string,
     *   newFaction: string,
     *   newClass: string,
     *   removedSkills: list<array{id: int|null, skillId: int|null, label: string|null, cost: int|null}>,
     *   unchanged: bool
     * }
     */
    public function changeFactionAndClassForStaff(
        Character $character,
        FactionType $newFaction,
        ClassType $newClass,
        User $actor
    ): array {
        if (!$actor->isOrga() && !$actor->isAdmin()) {
            throw new \Exception('Cette action est réservée aux organisateurs et aux administrateurs.');
        }

        $previousFaction = (string) $character->getFaction();
        $previousClass = $character->getClass()->value;

        if ($previousFaction === $newFaction->value && $previousClass === $newClass->value) {
            return [
                'previousFaction' => $previousFaction,
                'previousClass' => $previousClass,
                'newFaction' => $newFaction->value,
                'newClass' => $newClass->value,
                'removedSkills' => [],
                'unchanged' => true,
            ];
        }

        $toRemove = $this->getSkillLearnedToRemoveForFactionClassChange($character, $newFaction, $newClass);
        $removedPayload = $this->serializeRemovedSkillLearned($toRemove);

        foreach ($toRemove as $sl) {
            $this->entityManager->remove($sl);
        }

        $character->setFaction($newFaction->value);
        $character->setClass($newClass);
        $this->entityManager->flush();

        $this->logger->info('character_faction_class_changed_by_staff', [
            'character_id' => $character->getId(),
            'owner_user_id' => $character->getUser()?->getId(),
            'actor_id' => $actor->getId(),
            'actor_email' => $actor->getUserIdentifier(),
            'previous_faction' => $previousFaction,
            'previous_class' => $previousClass,
            'new_faction' => $newFaction->value,
            'new_class' => $newClass->value,
            'removed_skill_learned_ids' => array_map(fn (array $r) => $r['id'], $removedPayload),
            'removed_skill_ids' => array_map(fn (array $r) => $r['skillId'], $removedPayload),
        ]);

        return [
            'previousFaction' => $previousFaction,
            'previousClass' => $previousClass,
            'newFaction' => $newFaction->value,
            'newClass' => $newClass->value,
            'removedSkills' => $removedPayload,
            'unchanged' => false,
        ];
    }
}
