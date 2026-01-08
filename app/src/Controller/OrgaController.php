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
use App\Service\EmailService;
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\CharacterService;

#[IsGranted(RoleType::ROLE_ORGA)]
class OrgaController extends AbstractController
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        private EmailService $emailService,
        private CharacterService $characterService
    ) {
        $this->entityManager = $entityManager;
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
            $this->characterService->validateCharacter($character);
            $this->emailService->sendCharacterValidationEmail($character, 'validated');
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Une erreur est survenue lors de la validation du personnage.'], 500);
        }
    }

    #[Route('/api/character/{id}/reject', name: 'api_character_reject', methods: ['POST'])]
    public function rejectCharacter(Character $character): JsonResponse
    {
        try {
            $this->characterService->rejectCharacter($character);
            $this->emailService->sendCharacterValidationEmail($character, 'rejected');
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

            $this->emailService->sendContactEmail($user, $data['title'], $data['message'], $this->getUser());

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
            $dto->setClassFromLabel($data['class'] ?? '');
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

            $character = $this->characterService->createCharacter($dto, $user, true);

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/character/asset/{characterAssetId}/delete', name: 'api_character_asset_delete', methods: ['POST'])]
    public function deleteAsset(Request $request, $characterAssetId): JsonResponse
    {
        try {
            $characterAsset = $this->entityManager->getRepository(CharacterAsset::class)->find($characterAssetId);
            if (!$characterAsset) {
                return $this->json(['error' => 'Asset non trouvé'], 400);
            }
            $this->characterService->deleteAsset($characterAsset->getCharacter(), $characterAssetId);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/character/{id}/asset/add', name: 'api_character_asset_add', methods: ['POST'])]
    public function addAsset(Request $request, Character $character): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $this->characterService->addAsset($character, $data, true);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/character/{id}/skill/edit', name: 'api_character_skill_edit', methods: ['POST'])]
    public function editSkill(Request $request, Character $character): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (empty($data['skillId'])) {
                return $this->json(['error' => 'Tous les champs sont obligatoires'], 400);
            }
            $skill = $this->entityManager->getRepository(Skill::class)->find($data['skillId']);
            if (!$skill) {
                return $this->json(['error' => 'Compétence non trouvée'], 400);
            }
            $this->characterService->editSkill($character, $skill, $data['cost'], $data['note'], $data['noteOrga']);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/character/{id}/gear/edit', name: 'api_character_gear_edit', methods: ['POST'])]
    public function editGear(Request $request, Character $character): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (empty($data['possessionId'])) {
                return $this->json(['error' => 'Tous les champs sont obligatoires'], 400);
            }
            $this->characterService->editGear($character, $data['possessionId'], $data['cost'], $data['note'], $data['noteOrga']);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
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
        try {
            $data = json_decode($request->getContent(), true);
            if (empty($data['characterAssetId'])) {
                return $this->json(['error' => 'Tous les champs sont obligatoires'], 400);
            }
            $this->characterService->editAsset($character, $data['characterAssetId'], $data);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/mode-checkin', name: 'api_mode_checkin', methods: ['POST'])]
    public function modeCheckin(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!isset($data['modeCheckin'])) {
                throw new \Exception('Données manquantes');
            }

            $user = $this->getUser();
            /** @var User $user */
            error_log('modeCheckin data: ' . json_encode($data));
            $user->setModeCheckin((bool)$data['modeCheckin']);
            error_log('modeCheckin user: ' . json_encode($user->modeCheckin()));
            $this->entityManager->flush();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            error_log('modeCheckin error: ' . $e->getMessage());
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/orga/check-in/{id}', name: 'orga_checkin', methods: ['GET'])]
    public function checkIn(Character $character): Response
    {
        return $this->render('orga/checkin.html.twig', [
            'character' => $character,
        ]);
    }
}
