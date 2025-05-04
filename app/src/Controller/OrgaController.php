<?php

namespace App\Controller;

use App\Entity\RoleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Character;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\ValidationType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\FactionType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\User;
use App\Entity\EventType;
use App\Entity\CharacterType;
use App\Entity\CharacterAsset;
use App\Entity\Asset;
use App\Entity\Skill;
use App\Entity\SkillLearned;
use App\Entity\Possession;
use App\Entity\RarityType;
use App\Entity\AssetType;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\CreateCharacterDTO;

#[IsGranted(RoleType::ROLE_ORGA)]
class OrgaController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, private MailerInterface $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    #[Route('/orga', name: 'app_orga')]
    public function index(): Response
    {
        return $this->render('orga/index.html.twig', [
            'breadcrumb' => [
                '/orga' => 'Organisation',
            ],
        ]);
    }

    #[Route('/orga/characters', name: 'app_orga_characters')]
    public function characters(Request $request): Response
    {
        $where = ['validationType' => ValidationType::EN_COURS];
        $characters = $this->entityManager->getRepository(Character::class)->findBy($where);
        if ($this->getUser()->getFaction()) {
            $characters = array_filter($characters, function (Character $character) {
                return $character->getFaction() == $this->getUser()->getFaction()->value && $character->getUser()->getFaction() == null
                    || $character->getUser()->getFaction() == $this->getUser()->getFaction();
            });
        }
        return $this->render('orga/characters.html.twig', [
            'title' => 'Personnages en cours de validation',
            'breadcrumb' => [
                '/orga' => 'Organisation',
                '/orga/characters' => 'Gestion des personnages',
            ],
            'characters' => $characters
        ]);
    }

    #[Route('/orga/all_characters', name: 'app_orga_all_characters')]
    public function allCharacters(): Response
    {
        return $this->render('orga/characters.html.twig', [
            'title' => 'Tous les personnages',
            'breadcrumb' => ['/orga' => 'Organisation', '/orga/all_characters' => 'Tous les personnages'],
            'characters' => $this->entityManager->getRepository(Character::class)->findAll()
        ]);
    }

    #[Route('/orga/players', name: 'app_orga_players')]
    public function players(): Response
    {
        return $this->render('orga/players.html.twig', [
            'breadcrumb' => ['/orga' => 'Organisation', '/orga/players' => 'Liste des joueureuses'],
            'players' => $this->entityManager->getRepository(User::class)->findAll()
        ]);
    }

    #[Route('/orga/player/{id}', name: 'app_orga_player')]
    public function player(User $player): Response
    {
        return $this->render('orga/player.html.twig', [
            'user' => $player,
            'eventTypes' => EventType::getChoices(EventType::STATUS_OPEN),
            'breadcrumb' => ['/orga' => 'Organisation', '/orga/players' => 'Liste des joueureuses', '/orga/player/' . $player->getId() => $player->getFullName()],
            'factions' => FactionType::getChoices(),
        ]);
    }

    #[Route('/api/character/{id}/validate', name: 'api_character_validate', methods: ['POST'])]
    public function validateCharacter(Character $character): JsonResponse
    {
        try {
            $character->setValidationType(ValidationType::VALIDE);
            $this->entityManager->flush();

            $this->sendEmail(
                $character->getUser(),
                'Votre personnage a été validé. Vous pouvez le retrouver à l\'adresse suivante : ' . $this->generateUrl('app_character_sheet', ['id' => $character->getId()]),
                'Personnage validé'
            );

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Une erreur est survenue lors de la validation du personnage.'], 500);
        }
    }

    #[Route('/api/character/{id}/reject', name: 'api_character_reject', methods: ['POST'])]
    public function rejectCharacter(Character $character): JsonResponse
    {
        try {
            $character->setValidationType(ValidationType::NON_VALIDE);
            $this->entityManager->flush();

            $this->sendEmail(
                $character->getUser(),
                'Votre personnage n\'a pas été validé. Vous pouvez le retrouver à l\'adresse suivante : ' . $this->generateUrl('app_character_sheet', ['id' => $character->getId()]),
                'Personnage non validé'
            );

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Une erreur est survenue lors du rejet du personnage.'], 500);
        }
    }

    #[Route('/orga/character/{id}/edit', name: 'app_orga_character_edit')]
    public function editCharacter(Character $character): Response
    {
        return $this->render('orga/character_edit.html.twig', [
            'character' => $character,
            'navRelative' => false,
            'breadcrumb' => [
                $this->generateUrl('app_orga_characters') => 'Liste des personnages',
                'Fiche personnage : <b>' . $character->getName() . '</b>'
            ],
        ]);
    }

    private function sendEmail(User $user, string $message, string $title): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@dawn-gn.com', 'Dawn GN'))
            ->to($user->getEmail())
            ->subject('[Dawn GN] - ' . $title)
            ->htmlTemplate('contact/orga_email.html.twig')
            ->context([
                'message' => $message,
                'title' => $title,
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }

    #[Route('/api/contact_player', name: 'api_contact_player', methods: ['POST'])]
    public function contactPlayer(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            if (empty($data['userId'] ?? null) || empty($data['message'] ?? null) || empty($data['title'] ?? null)) {
                throw new \Exception('Données manquantes');
            }

            $user = $this->entityManager->getRepository(User::class)->find($data['userId']);
            if (!$user) {
                throw new \Exception('Utilisateur non trouvé');
            }

            $this->sendEmail($user, $data['message'], $data['title']);

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/create_character', name: 'app_orga_character_create', methods: ['POST'])]
    public function createCharacter(Request $request, ValidatorInterface $validator): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Données JSON invalides');
            }

            $dto = new CreateCharacterDTO();
            $dto->setCharacterName($data['character_name'] ?? '');
            $dto->setFaction($data['faction'] ?? '');
            $dto->setClass($data['class'] ?? '');
            $dto->setUserId((int)($data['userId'] ?? 0));
            $dto->setBackground($data['background'] ?? null);

            $errors = $validator->validate($dto);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                throw new \Exception(implode(', ', $errorMessages));
            }

            $user = $this->entityManager->getRepository(User::class)->find($dto->getUserId());
            if (!$user) {
                throw new \Exception('Utilisateur non trouvé');
            }

            // Validation des valeurs de faction et classe
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

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/character/asset/{characterAssetId}/delete', name: 'api_character_asset_delete', methods: ['POST'])]
    public function deleteAsset(Request $request, $characterAssetId): JsonResponse
    {
        $characterAsset = $this->entityManager->getRepository(CharacterAsset::class)->find($characterAssetId);
        if (!$characterAsset) {
            return $this->json(['error' => 'Équipement non trouvé'], 400);
        }
        $this->entityManager->remove($characterAsset);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/character/{id}/asset/add', name: 'api_character_asset_add', methods: ['POST'])]
    public function addAsset(Request $request, Character $character): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['assetId']) && (!$data['isCustom'] || empty($data['assetName']))) {
            return $this->json(['error' => 'Tous les champs sont obligatoires'], 400);
        }

        if ($data['isCustom']) {
            // create custom asset
            $customAsset = new Asset();
            $customAsset->setLabel($data['assetName']);
            $customAsset->setIsCatalog(false);
            $customAsset->setBaseCost(0);
            $customAsset->setQuote('');
            $customAsset->setRequiredClasses(['']);
            $customAsset->setRequiredFactions(['']);
            $customAsset->setVisibility(true);
            $customAsset->setBaseNote($data['assetNote']);
            $customAsset->setBaseNoteOrga($data['assetNoteOrga']);
            $customAsset->setDescription($data['assetDescription']);
            $customAsset->setType(AssetType::fromString($data['assetType']));
            $customAsset->setShort($data['assetShort']);
            $customAsset->setRarity(RarityType::UNIQUE);

            $this->entityManager->persist($customAsset);
            $this->entityManager->flush();

            $asset = $customAsset;
        } else {
            $asset = $this->entityManager->getRepository(Asset::class)->find($data['assetId']);
            if (!$asset) {
                return $this->json(['error' => 'Assets non trouvé'], 400);
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

        return $this->json(['success' => true]);
    }

    #[Route('/api/character/{id}/skill/edit', name: 'api_character_skill_edit', methods: ['POST'])]
    public function editSkill(Request $request, Character $character): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['skillId'])) {
            return $this->json(['error' => 'Tous les champs sont obligatoires'], 400);
        }

        $skill = $this->entityManager->getRepository(Skill::class)->find($data['skillId']);
        if (!$skill) {
            return $this->json(['error' => 'Compétence non trouvée'], 400);
        }

        $skillLearned = $this->entityManager->getRepository(SkillLearned::class)->findOneBy(['character' => $character, 'skill' => $skill]);
        if (!$skillLearned) {
            return $this->json(['error' => 'Compétence non trouvée'], 400);
        }

        $skillLearned->setCost($data['cost']);
        $skillLearned->setNote($data['note']);
        $skillLearned->setNoteOrga($data['noteOrga']);

        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/character/{id}/gear/edit', name: 'api_character_gear_edit', methods: ['POST'])]
    public function editGear(Request $request, Character $character): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['possessionId'])) {
            return $this->json(['error' => 'Tous les champs sont obligatoires'], 400);
        }

        $possession = $this->entityManager->getRepository(Possession::class)->find($data['possessionId']);
        if (!$possession) {
            return $this->json(['error' => 'Équipement non trouvé'], 400);
        }

        $possession->setCost($data['cost']);
        $possession->setNote($data['note']);
        $possession->setNoteOrga($data['noteOrga']);

        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/character/{id}/assets', name: 'api_character_available_assets', methods: ['GET'])]
    public function getAvailableAssets(Character $character): JsonResponse
    {
        $assetsInCatalog = $this->entityManager->getRepository(Asset::class)->findBy(['is_catalog' => true]);
        $availableAssets = [];
        foreach ($assetsInCatalog as $asset) {
            $availableAssets[] = [
                'id' => $asset->getId(),
                'label' => $asset->getLabel(),
                'description' => $asset->getDescription(),
                'short' => $asset->getShort(),
                'type' => $asset->getType(),
                'baseNote' => $asset->getBaseNote(),
                'baseNoteOrga' => $asset->getBaseNoteOrga(),
            ];
        }
        usort($availableAssets, function ($a, $b) {
            return strcmp($a['label'], $b['label']);
        });
        return $this->json(['success' => true, 'assets' => $availableAssets]);
    }

    #[Route('/api/character/{id}/asset/edit', name: 'api_character_asset_edit', methods: ['POST'])]
    public function editAsset(Request $request, Character $character): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['characterAssetId'])) {
            return $this->json(['error' => 'Tous les champs sont obligatoires'], 400);
        }

        $characterAsset = $this->entityManager->getRepository(CharacterAsset::class)->find($data['characterAssetId']);
        if (!$characterAsset) {
            return $this->json(['error' => 'Asset non trouvé'], 400);
        }

        if ($characterAsset->getAsset()->isIsCatalog()) {
            $asset = $characterAsset->getAsset();
            $asset->setLabel($data['assetName']);
            $asset->setDescription($data['assetDescription']);
            $asset->setShort($data['assetShort']);
            $this->entityManager->flush();
        }

        $characterAsset->setNote($data['note']);
        $characterAsset->setNoteOrga($data['noteOrga']);

        $asset = $characterAsset->getAsset();
        if ($asset->getType() == AssetType::OBJECT) {
            $characterAsset->setQuantity($data['assetQuantity']);
        }

        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }
}
