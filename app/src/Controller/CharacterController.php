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

#[Route('/characters')]
#[IsGranted('ROLE_USER')]
class CharacterController extends AbstractController
{
    private LoggerInterface $logger;
    public function __construct(
        private EntityManagerInterface $entityManager,
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
        ]);
    }

    #[Route('/new', name: 'app_character_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Récupérer les données soit du formulaire, soit du JSON
            $data = $request->getContent() ? json_decode($request->getContent(), true) : $request->request->all();

            if (empty($data['character_name']) || empty($data['faction']) || empty($data['class'])) {
                return $this->json(['error' => 'Tous les champs sont obligatoires'], 400);
            }

            $character = new Character();
            $character->setUser($this->getUser());
            $character->setName($data['character_name']);
            $character->setFaction($data['faction']);
            $character->setClass($data['class']);
            $character->setBackground($data['background'] ?? '');
            $character->setDescription(''); // Description vide par défaut
            $character->setNoteOrga(''); // Note orga vide par défaut
            $character->setXpSkill(20);
            $character->setXpGear(10);
            $character->setType(CharacterType::DRAFT);
            $character->setValidationType(ValidationType::NON_VALIDE);

            $this->entityManager->persist($character);
            $this->entityManager->flush();

            if ($request->getContent()) {
                return $this->json(['success' => true]);
            }

            $this->addFlash('success', 'Votre personnage a été créé avec succès.');
            return $this->redirectToRoute('app_character_index');
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
        // redirect to edit page
        return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
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

    private function checkCharacterValidation(Character $character): void
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

    #[Route('/{id}/skill/add', name: 'character_skill_add', methods: ['GET', 'POST'])]
    public function addSkill(Request $request, Character $character): Response
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce personnage.');
        }

        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->checkCharacterValidation($character);
        } catch (\Exception $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
        }

        if ($request->isMethod('POST')) {
            $skillId = $request->request->get('skill_id');
            if (!$skillId) {
                $this->addFlash('error', 'Aucune compétence sélectionnée.');
                return $this->redirectToRoute('character_skill_add', ['id' => $character->getId()]);
            }

            $skill = $this->entityManager->getRepository(Skill::class)->find($skillId);
            if (!$skill) {
                $this->addFlash('error', 'Compétence non trouvée.');
                return $this->redirectToRoute('character_skill_add', ['id' => $character->getId()]);
            }

            // Vérifier si le personnage a déjà cette compétence
            foreach ($character->getSkillsLearned() as $learnedSkill) {
                if ($learnedSkill->getSkill()->getId() === $skill->getId()) {
                    $this->addFlash('error', 'Vous avez déjà cette compétence.');
                    return $this->redirectToRoute('character_skill_add', ['id' => $character->getId()]);
                }
            }

            // Vérifier si la compétence est disponible pour le personnage
            if (!$this->entityManager->getRepository(Skill::class)->isSkillAvailableForCharacter($character, $skill)) {
                $this->addFlash('error', 'Cette compétence n\'est pas disponible pour ce personnage.');
                return $this->redirectToRoute('character_skill_add', ['id' => $character->getId()]);
            }

            // Vérifier si le personnage a suffisamment de points d'action
            if ($character->getAvailableSkillsXp() < $skill->getBaseCost()) {
                $this->addFlash('error', 'Vous n\'avez pas assez de points d\'action pour apprendre cette compétence.');
                return $this->redirectToRoute('character_skill_add', ['id' => $character->getId()]);
            }

            // Créer la nouvelle compétence apprise
            $skillLearned = new SkillLearned();
            $skillLearned->setSkill($skill);
            $skillLearned->setCost($skill->getBaseCost());
            $skillLearned->setNote($request->request->get('note', ''));
            $skillLearned->setCharacter($character);
            $this->entityManager->persist($skillLearned);
            $this->entityManager->flush();

            $this->addFlash('success', 'La compétence a été ajoutée avec succès.');
            return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
        }

        // Récupérer toutes les compétences disponibles
        $availableSkills = $this->entityManager->getRepository(Skill::class)->findAvailableSkillsForCharacter($character);

        return $this->render('character/skill_add.html.twig', [
            'character' => $character,
            'skills' => $availableSkills,
        ]);
    }

    #[Route('/{id}/skill/{skillId}/delete', name: 'character_skill_delete', methods: ['GET'])]
    public function deleteSkill(Character $character, int $skillId): Response
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce personnage.');
        }

        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->checkCharacterValidation($character);
        } catch (\Exception $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
        }

        // Trouver la compétence apprise
        $skillLearned = null;
        foreach ($character->getSkillsLearned() as $learnedSkill) {
            if ($learnedSkill->getSkill()->getId() === $skillId) {
                $skillLearned = $learnedSkill;
                break;
            }
        }

        if (!$skillLearned) {
            $this->addFlash('error', 'Cette compétence n\'a pas été trouvée.');
            return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
        }

        // Supprimer la compétence
        $this->entityManager->remove($skillLearned);
        $this->entityManager->flush();

        $this->addFlash('success', 'La compétence a été supprimée avec succès.');
        return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
    }

    #[Route('/{id}/equipment/add', name: 'character_equipment_add', methods: ['GET', 'POST'])]
    public function addEquipment(Request $request, Character $character): Response
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce personnage.');
        }

        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->checkCharacterValidation($character);
        } catch (\Exception $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
        }

        if ($request->isMethod('POST')) {
            $gearId = $request->request->get('gear_id');
            if (!$gearId) {
                $this->addFlash('error', 'Aucun équipement sélectionné.');
                return $this->redirectToRoute('character_equipment_add', ['id' => $character->getId()]);
            }

            $gear = $this->entityManager->getRepository(Gear::class)->find($gearId);
            if (!$gear) {
                $this->addFlash('error', 'Équipement non trouvé.');
                return $this->redirectToRoute('character_equipment_add', ['id' => $character->getId()]);
            }

            // Vérifier si le personnage a assez d'XP
            if ($gear->getBaseCost() > ($character->getXpGear() - $character->getGearXpUsed())) {
                return $this->json(['error' => 'Points d\'XP insuffisants.'], 400);
            }

            // Créer la nouvelle possession
            $possession = new Possession();
            $possession->setCharacter($character);
            $possession->setGear($gear);
            $possession->setCost($gear->getBaseCost());
            $possession->setNote('');
            $possession->setNoteOrga('');

            $this->entityManager->persist($possession);
            $this->entityManager->flush();

            $this->addFlash('success', 'L\'équipement a été ajouté avec succès.');
            return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
        }

        // Récupérer tous les équipements disponibles
        $availableGear = $this->entityManager->getRepository(Gear::class)->findBy(['visibility' => true]);

        return $this->render('character/equipment_add.html.twig', [
            'character' => $character,
            'gear' => $availableGear,
        ]);
    }

    #[Route('/{id}/equipment/delete/{possessionId}', name: 'character_equipment_delete', methods: ['GET'])]
    public function deleteEquipment(Character $character, int $possessionId): Response
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce personnage.');
        }

        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->checkCharacterValidation($character);
        } catch (\Exception $e) {
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
        }

        // Rechercher la possession
        $possession = null;
        foreach ($character->getPossessions() as $p) {
            if ($p->getId() === $possessionId) {
                $possession = $p;
                break;
            }
        }

        if (!$possession) {
            $this->addFlash('error', 'Équipement non trouvé.');
            return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
        }

        $this->entityManager->remove($possession);
        $this->entityManager->flush();

        $this->addFlash('success', 'L\'équipement a été supprimé avec succès.');
        return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
    }

    #[Route('/{id}/delete', name: 'character_delete', methods: ['POST'])]
    public function delete(Character $character): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Vous n\'êtes pas autorisé à supprimer ce personnage.'], 403);
        }

        $this->entityManager->remove($character);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/submit', name: 'character_submit', methods: ['POST'])]
    public function submit(Character $character): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Vous n\'êtes pas autorisé à soumettre ce personnage.'], 403);
        }

        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->checkCharacterValidation($character);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        // Vérifier que le personnage a un nom, une faction et une classe
        if (empty($character->getName()) || empty($character->getFaction()) || empty($character->getClass())) {
            return $this->json(['error' => 'Votre personnage doit avoir un nom, une faction et une classe avant d\'être soumis.'], 400);
        }

        $character->setValidationType(ValidationType::EN_COURS);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/background', name: 'app_character_background', methods: ['GET', 'POST'])]
    public function background(Request $request, Character $character): Response
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce personnage.');
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
            $character->setDescription($request->request->get('description', ''));
            $character->setBackground($request->request->get('background', ''));

            $this->entityManager->flush();

            $this->addFlash('success', 'Le background a été sauvegardé avec succès.');
            return $this->redirectToRoute('app_character_background', ['id' => $character->getId()]);
        }

        return $this->render('character/background.html.twig', [
            'character' => $character,
        ]);
    }

    #[Route('/api/character/{id}/available-skills', name: 'api_character_available_skills', methods: ['GET'])]
    public function getAvailableSkills(Character $character): JsonResponse
    {
        try {
            $skills = $this->entityManager->getRepository(Skill::class)->findAvailableSkillsForCharacter($character);

            if (!$skills) {
                return $this->json(['error' => 'Aucune compétence disponible pour ce personnage.'], 404);
            }

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

        try {
            if ($this->entityManager->getRepository(Skill::class)->isSkillAvailableForCharacter($skill, $character)) {
                $character->addSkill($skill);
                $this->entityManager->flush();
            } else {
                throw new \Exception('Cette compétence n\'est pas disponible pour ce personnage');
            }
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

        if (!$skillId) {
            return $this->json(['error' => 'Paramètre skillId manquant'], 400);
        }

        $skillLearned = $character->getSkillsLearned()->filter(
            fn($skillLearned) => $skillLearned->getSkill()->getId() === $skillId
        )->first();

        if (!$skillLearned) {
            return $this->json(['error' => 'Cette compétence n\'est pas apprise par ce personnage'], 404);
        }

        $this->entityManager->remove($skillLearned);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/character/{id}/skill/xp/add', name: 'api_character_skill_xp_add', methods: ['POST'])]
    public function addSkillXpApi(Character $character, Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Vous n\'êtes pas autorisé à modifier ce personnage.'], 403);
        }

        // Vérifier que le personnage n'a pas été rejeté
        if ($character->getValidationType() === ValidationType::REJETE) {
            return $this->json(['error' => 'Ce personnage a été rejeté.'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $xp = $data['xp'] ?? null;

        if (!$xp || !is_numeric($xp) || $xp <= 0) {
            return $this->json(['error' => 'Valeur d\'XP invalide'], 400);
        }

        $availableXp = $character->getAvailableXp();
        if ($xp > $availableXp) {
            return $this->json(['error' => 'Points d\'XP insuffisants'], 400);
        }

        try {
            $character->setXpSkill($character->getXpSkill() + $xp);
            $this->entityManager->flush();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de l\'ajout des points d\'XP'], 500);
        }
    }

    #[Route('/api/character/{id}/gear/delete', name: 'api_character_gear_delete', methods: ['POST'])]
    public function deleteGearApi(Character $character, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $possessionId = $data['possessionId'] ?? null;

        if (!$possessionId) {
            return $this->json(['error' => 'Aucun équipement sélectionné.'], 400);
        }

        $possession = $this->entityManager->getRepository(Possession::class)->find($possessionId);
        if (!$possession) {
            return $this->json(['error' => 'Équipement non trouvé.'], 404);
        }

        $this->entityManager->remove($possession);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/api/character/{id}/gear/xp/add', name: 'api_character_gear_xp_add', methods: ['POST'])]
    public function addGearXpApi(Character $character, Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Vous n\'êtes pas autorisé à modifier ce personnage.'], 403);
        }

        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->checkCharacterValidation($character);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $data = json_decode($request->getContent(), true);
        $xp = $data['xp'] ?? null;

        if (!$xp || !is_numeric($xp) || $xp <= 0) {
            return $this->json(['error' => 'Valeur d\'XP invalide'], 400);
        }

        $availableXp = $character->getAvailableXp();
        if ($xp > $availableXp) {
            return $this->json(['error' => 'Points d\'XP insuffisants'], 400);
        }

        try {
            $character->setXpGear($character->getXpGear() + $xp);
            $this->entityManager->flush();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de l\'ajout des points d\'XP'], 500);
        }
    }

    #[Route('/api/character/{id}/name/update', name: 'api_character_name_update', methods: ['POST'])]
    public function updateNameApi(Character $character, Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Vous n\'êtes pas autorisé à modifier ce personnage.'], 403);
        }

        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->checkCharacterValidation($character);
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
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Vous n\'êtes pas autorisé à modifier ce personnage.'], 403);
        }

        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->checkCharacterValidation($character);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $data = json_decode($request->getContent(), true);
        $gearId = $data['gearId'] ?? null;

        if (!$gearId) {
            return $this->json(['error' => 'Aucun équipement sélectionné.'], 400);
        }

        $gear = $this->entityManager->getRepository(Gear::class)->find($gearId);
        if (!$gear) {
            return $this->json(['error' => 'Équipement non trouvé.'], 404);
        }

        // Vérifier si le personnage a assez d'XP
        if ($gear->getBaseCost() > ($character->getXpGear() - $character->getGearXpUsed())) {
            return $this->json(['error' => 'Points d\'XP insuffisants.'], 400);
        }

        try {
            // Créer la nouvelle possession
            $possession = new Possession();
            $possession->setCharacter($character);
            $possession->setGear($gear);
            $possession->setCost($gear->getBaseCost());
            $possession->setNote('');
            $possession->setNoteOrga('');

            $this->entityManager->persist($possession);
            $this->entityManager->flush();

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de l\'ajout de l\'équipement.', 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/set-type/{type}', name: 'character_set_type', methods: ['POST'])]
    public function setType(Character $character, string $type): JsonResponse
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Vous n\'êtes pas autorisé à modifier ce personnage.'], 403);
        }

        // Vérifier que le personnage n'est pas déjà validé
        try {
            $this->checkCharacterValidation($character);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        // Vérifier que le type est valide
        if (!in_array($type, ['main', 'secondary'])) {
            return $this->json(['error' => 'Type de personnage invalide.'], 400);
        }

        // Vérifier que l'utilisateur n'a pas déjà un personnage du type demandé
        if ($type === 'main' && $this->getUser()->hasMainCharacter()) {
            return $this->json(['error' => 'Vous avez déjà un personnage principal.'], 400);
        }

        if ($type === 'secondary' && $this->getUser()->hasSecondaryCharacter()) {
            return $this->json(['error' => 'Vous avez déjà un personnage secondaire.'], 400);
        }

        // Définir le type du personnage
        $character->setType($type === 'main' ? CharacterType::MAIN : CharacterType::SECONDARY);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }
}
