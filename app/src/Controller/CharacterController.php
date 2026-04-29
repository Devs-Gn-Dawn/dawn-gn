<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\FactionType;
use App\Entity\ClassType;
use App\Repository\CharacterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Skill;
use App\Entity\SkillLearned;
use App\Entity\Gear;
use App\Entity\Possession;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Psr\Log\LoggerInterface;
use App\Entity\CharacterType;
use App\Entity\ValidationType;
use App\DTO\CreateCharacterDTO;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\CharacterService;

#[Route('/characters')]
#[IsGranted('ROLE_USER')]
class CharacterController extends AbstractController
{
    private LoggerInterface $logger;
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CharacterService $characterService,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_character_index', methods: ['GET'])]
    public function index(CharacterRepository $characterRepository): Response
    {
        return $this->render('character/index.html.twig', [
            'characters' => $characterRepository->findBy(['user' => $this->getUser()]),
            'breadcrumb' => ['Liste des personnages'],
            'factions' => FactionType::getChoices(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/new', name: 'app_character_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            try {
                // Récupérer les données soit du formulaire, soit du JSON
                $data = $request->getContent() ? json_decode($request->getContent(), true) : $request->request->all();
                if (!$data) {
                    throw new \Exception('Données invalides');
                }

                $dto = new CreateCharacterDTO();
                $dto->setCharacterName($data['character_name'] ?? '');
                $dto->setFaction($data['faction'] ?? '');
                $dto->setClassFromLabel($data['class'] ?? '');
                $dto->setUserId($this->getUser()->getId());
                $dto->setBackground($data['background'] ?? null);

                $errors = $validator->validate($dto);
                if (count($errors) > 0) {
                    $errorMessages = [];
                    foreach ($errors as $error) {
                        $errorMessages[] = $error->getMessage();
                    }
                    throw new \Exception(implode(', ', $errorMessages));
                }

                $character = $this->characterService->createCharacter($dto, $this->getUser());

                if ($request->getContent()) {
                    return $this->json(['success' => true]);
                }

                $this->addFlash('success', 'Votre personnage a été créé avec succès.');
                return $this->redirectToRoute('app_character_index');
            } catch (\Exception $e) {
                if ($request->getContent()) {
                    return $this->json(['error' => $e->getMessage()], 400);
                }
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_character_new');
            }
        }

        return $this->render('character/new.html.twig', [
            'factions' => FactionType::getChoices(),
            'classes' => ClassType::getChoices(),
        ]);
    }

    #[Route('/main', name: 'app_character_main', methods: ['GET'])]
    public function main(CharacterRepository $characterRepository): Response
    {
        // check if user has a main character
        $mainCharacter = $characterRepository->findOneBy(['user' => $this->getUser(), 'type' => CharacterType::MAIN]);
        if (!$mainCharacter) {
            return $this->redirectToRoute('app_character_index');
        }

        return $this->redirectToRoute('app_character_edit', ['id' => $mainCharacter->getId()]);
    }

    #[Route('/{id}/check', name: 'app_character_check', methods: ['GET'])]
    public function check(Character $character): Response
    {
        $user = $this->getUser();
        $route = 'app_character_edit';
        /** @var User $user */
        if ($user->isOrga() && $user->modeCheckin()) {
            $route = 'orga_checkin';
        } elseif($user->isOrga()) {
            $route = 'app_orga_character_edit';
        }
        // redirect to edit page
        return $this->redirectToRoute($route, ['id' => $character->getId()]);
    }

    #[Route('/{id}/edit', name: 'app_character_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, CharacterRepository $characterRepository): Response
    {
        // Récupérer le personnage par son ID
        $character = $characterRepository->find($id);

        // Rediriger si le personnage n'existe pas
        if (!$character) {
            return $this->redirectToRoute('app_character_index');
        }

        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce personnage.');
        }

        return $this->render('character/edit.html.twig', [
            'character' => $character,
            'navRelative' => false,
            'breadcrumb' => [
                $this->generateUrl('app_character_index') => 'Liste des personnages',
                'Fiche personnage : <b>' . $character->getName() . '</b>'
            ],
        ]);
    }

    #[Route('/api/classes/{faction}', name: 'api_classes_by_faction', methods: ['GET'])]
    public function getClassesByFaction(FactionType $faction): JsonResponse
    {
        return $this->json(ClassType::getChoicesForFaction($faction));
    }


    #[Route('/{id}/delete', name: 'character_delete', methods: ['POST'])]
    public function delete(Character $character): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        if ($character->isMain()) {
            $owner = $character->getUser();
            foreach ($owner->getCharacters() as $other) {
                if ($other !== $character && $other->isSecondary()) {
                    $other->setType(CharacterType::MAIN);
                    break;
                }
            }
        }

        // Supprimer les possessions
        foreach ($character->getPossessions() as $possession) {
            $this->entityManager->remove($possession);
        }

        // Supprimer les compétences apprises
        foreach ($character->getSkillsLearned() as $skillLearned) {
            $this->entityManager->remove($skillLearned);
        }

        // Supprimer les assets
        foreach ($character->getCharacterAssets() as $characterAsset) {
            $this->entityManager->remove($characterAsset);
        }

        // Supprimer le personnage
        $this->entityManager->remove($character);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/submit', name: 'character_submit', methods: ['POST'])]
    public function submit(Character $character): JsonResponse
    {
        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
            $this->characterService->checkCharacterValidation($character);
            // Vérifier que le personnage a un nom, une faction et une classe
            if (empty($character->getName()) || empty($character->getFaction()) || empty($character->getClass())) {
                throw new \Exception('Votre personnage doit avoir un nom, une faction et une classe avant d\'être soumis.');
            }
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $character->setValidationType(ValidationType::EN_COURS);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/character/{id}/background', name: 'app_character_background', methods: ['GET', 'POST'])]
    public function background(Request $request, Character $character): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }


        if ($request->isMethod('POST')) {
            // Si la requête est en JSON
            if ($request->headers->get('Content-Type') === 'application/json') {
                $data = json_decode($request->getContent(), true);
                $background = $data['background'] ?? '';

                $character->setBackground($background);
                $this->entityManager->flush();

                return $this->json(['success' => true]);
            }

            // Sinon, traitement du formulaire classique
            // $character->setDescription($request->request->get('description', ''));
            $character->setBackground($request->request->get('background', ''));

            $this->entityManager->flush();

            $this->addFlash('success', 'Le background a été sauvegardé avec succès.');
            return $this->json(['success' => true]);
        }

        return $this->json(['error' => 'Méthode non autorisée'], 405);
    }

    #[Route('/api/character/{id}/available-skills', name: 'api_character_available_skills', methods: ['GET'])]
    public function getAvailableSkills(Character $character): JsonResponse
    {
        try {
            $skills = $this->entityManager->getRepository(Skill::class)->findAvailableSkillsForCharacter($character);

            if (!$skills) {
                return $this->json(['error' => 'Aucune compétence disponible pour ce personnage.'], 404);
            }

            // sort by label
            usort($skills, function ($a, $b) {
                return strcmp($a->getLabel(), $b->getLabel());
            });

            return $this->json($skills);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors du chargement des compétences disponibles.', 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/character/{id}/skill/add', name: 'api_character_skill_add', methods: ['POST'])]
    public function addSkillApi(Character $character, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $skill = $this->entityManager->getRepository(Skill::class)->find($data['skillId']);
        $isOrga = $this->getUser()->isOrga();

        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
            $this->characterService->addSkill(
                $character,
                $skill,
                $isOrga ? $data['skillCost'] ?? null : null,
                $isOrga ? $data['skillNote'] ?? null : null,
                $isOrga ? $data['skillNoteOrga'] ?? null : null,
                $isOrga
            );
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/api/character/{id}/skill/delete', name: 'api_character_skill_delete', methods: ['POST'])]
    public function deleteSkillApi(Character $character, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $skillId = $data['skillId'] ?? null;

        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
            if (!$skillId) {
                throw new \Exception('Paramètre skillId manquant');
            }
            $this->characterService->deleteSkill($character, $skillId, $this->getUser()->isOrga());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json(['success' => true]);
    }


    #[Route('/api/character/{id}/skill/xp/remove', name: 'api_character_skill_xp_remove', methods: ['POST'])]
    public function removeSkillXpApi(Character $character, Request $request): JsonResponse
    {
        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
            $data = json_decode($request->getContent(), true);
            $xp = $data['xp'] ?? null;
            if (!$xp || !is_numeric($xp) || $xp <= 0) {
                throw new \Exception('Valeur d\'XP invalide');
            }
            $this->characterService->removeSkillXp($character, $xp);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/character/{id}/skill/xp/add', name: 'api_character_skill_xp_add', methods: ['POST'])]
    public function addSkillXpApi(Character $character, Request $request): JsonResponse
    {
        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
            $data = json_decode($request->getContent(), true);
            $xp = $data['xp'] ?? null;
            if (!$xp || !is_numeric($xp) || $xp <= 0) {
                throw new \Exception('Valeur d\'XP invalide');
            }
            $this->characterService->addSkillXp($character, $xp);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/character/{id}/gear/delete', name: 'api_character_gear_delete', methods: ['POST'])]
    public function deleteGearApi(Character $character, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $possessionId = $data['possessionId'] ?? null;

        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
            if (!$possessionId) {
                throw new \Exception('Aucun équipement sélectionné.');
            }
            $this->characterService->deleteGear($character, $possessionId, $this->getUser()->isOrga());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/api/character/{id}/gear/xp/add', name: 'api_character_gear_xp_add', methods: ['POST'])]
    public function addGearXpApi(Character $character, Request $request): JsonResponse
    {
        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
            $data = json_decode($request->getContent(), true);
            $xp = $data['xp'] ?? null;
            if (!$xp || !is_numeric($xp) || $xp <= 0) {
                throw new \Exception('Valeur d\'XP invalide');
            }
            $this->characterService->addGearXp($character, $xp);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/character/{id}/gear/xp/remove', name: 'api_character_gear_xp_remove', methods: ['POST'])]
    public function removeGearXpApi(Character $character, Request $request): JsonResponse
    {
        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
            $data = json_decode($request->getContent(), true);
            $xp = $data['xp'] ?? null;
            if (!$xp || !is_numeric($xp) || $xp <= 0) {
                throw new \Exception('Valeur d\'XP invalide');
            }
            $this->characterService->removeGearXp($character, $xp);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/character/{id}/name/update', name: 'api_character_name_update', methods: ['POST'])]
    public function updateNameApi(Character $character, Request $request): JsonResponse
    {
        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
            $this->characterService->checkCharacterValidation($character);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;

        if (!$name || empty(trim($name))) {
            return $this->json(['error' => 'Le nom ne peut pas être vide'], 400);
        }

        try {
            $character->setName(trim($name));
            $this->entityManager->flush();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la mise à jour du nom'], 500);
        }
    }

    #[Route('/api/character/{id}/available-gear', name: 'api_character_available_gear', methods: ['GET'])]
    public function getAvailableGear(Character $character): JsonResponse
    {
        try {
            $gear = $this->entityManager->getRepository(Gear::class)->findBy(['visibility' => true]);

            if (!$gear) {
                return $this->json(['error' => 'Aucun équipement disponible.'], 404);
            }

            return $this->json($gear);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors du chargement des équipements disponibles.', 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/character/{id}/gear/add', name: 'api_character_gear_add', methods: ['POST'])]
    public function addGearApi(Character $character, Request $request): JsonResponse
    {
        $isOrga = $this->getUser()->isOrga();
        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
            $data = json_decode($request->getContent(), true);
            $gearId = $data['gearId'] ?? null;
            if (!$gearId) {
                throw new \Exception('Aucun équipement sélectionné.');
            }
            $gear = $this->entityManager->getRepository(Gear::class)->find($gearId);
            if (!$gear) {
                throw new \Exception('Équipement non trouvé.');
            }
            $this->characterService->addGear(
                $character,
                $gear,
                $isOrga ? ($data['gearCost'] ?? null) : $gear->getBaseCost(),
                $isOrga ? $data['gearNote'] ?? null : '',
                $isOrga ? $data['gearNoteOrga'] ?? null : '',
                $isOrga
            );
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}/set-type/{type}', name: 'character_set_type', methods: ['POST'])]
    public function setType(Character $character, string $type): JsonResponse
    {
        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->characterService->checkCharacterAccess($character, $this->getUser());
            $this->characterService->checkCharacterValidation($character);
            // Vérifier que le type est valide
            if (!in_array($type, ['main', 'secondary'])) {
                throw new \Exception('Type de personnage invalide.');
            }

            // Vérifier que l'utilisateur n'a pas déjà un personnage du type demandé
            if ($type === 'main' && $this->getUser()->hasMainCharacter()) {
                throw new \Exception('Vous avez déjà un personnage principal.');
            }

            if ($type === 'secondary' && $this->getUser()->hasSecondaryCharacter()) {
                throw new \Exception('Vous avez déjà un personnage secondaire.');
            }
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }


        // Définir le type du personnage
        $character->setType($type === 'main' ? CharacterType::MAIN : CharacterType::SECONDARY);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }
}
