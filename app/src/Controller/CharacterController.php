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

#[Route('/characters')]
#[IsGranted('ROLE_USER')]
class CharacterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'app_character_index', methods: ['GET'])]
    public function index(CharacterRepository $characterRepository): Response
    {
        return $this->render('character/index.html.twig', [
            'characters' => $characterRepository->findBy(['user' => $this->getUser()]),
            'breadcrumb' => ['Liste des personnages'],
        ]);
    }

    #[Route('/new', name: 'app_character_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $character = new Character();
            $character->setUser($this->getUser());
            $character->setName($request->request->get('character_name'));
            $character->setFaction($request->request->get('faction'));
            $character->setClass($request->request->get('class'));
            $character->setBackground($request->request->get('background'));
            $character->setDescription(''); // Description vide par défaut
            $character->setNoteOrga(''); // Note orga vide par défaut
            $character->setIsMain(false);
            $character->setIsValidated(false);

            $this->entityManager->persist($character);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre personnage a été créé avec succès.');
            return $this->redirectToRoute('app_character_index');
        }

        return $this->render('character/new.html.twig', [
            'factions' => FactionType::getChoices(),
            'classes' => ClassType::getChoices(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_character_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Character $character): Response
    {
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

    #[Route('/{id}/skill/add', name: 'character_skill_add', methods: ['GET', 'POST'])]
    public function addSkill(Request $request, Character $character): Response
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce personnage.');
        }

        // Vérifier que le personnage n'est pas déjà validé
        if ($character->isValidated()) {
            $this->addFlash('warning', 'Ce personnage est déjà validé.');
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

            // Créer la nouvelle compétence apprise
            $skillLearned = new SkillLearned();
            $skillLearned->setCharacter($character);
            $skillLearned->setSkill($skill);
            $skillLearned->setCost($skill->getBaseCost());
            $skillLearned->setNote('');
            $skillLearned->setNoteOrga('');

            $this->entityManager->persist($skillLearned);
            $this->entityManager->flush();

            $this->addFlash('success', 'La compétence a été ajoutée avec succès.');
            return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
        }

        // Récupérer toutes les compétences disponibles
        $availableSkills = $this->entityManager->getRepository(Skill::class)->findBy(['visibility' => true]);

        // Filtrer les compétences en fonction des prérequis
        $filteredSkills = [];
        foreach ($availableSkills as $skill) {
            // Vérifier les prérequis de classe
            if (!empty($skill->getRequiredClasses()) && !in_array($character->getClass(), $skill->getRequiredClasses())) {
                continue;
            }

            // Vérifier les prérequis de faction
            if (!empty($skill->getRequiredFactions()) && !in_array($character->getFaction(), $skill->getRequiredFactions())) {
                continue;
            }

            // Vérifier les prérequis de compétences
            $hasRequiredSkills = true;
            foreach ($skill->getRequiredSkills() as $requiredSkill) {
                $hasLearned = false;
                foreach ($character->getSkillsLearned() as $learnedSkill) {
                    if ($learnedSkill->getSkill()->getId() === $requiredSkill->getId()) {
                        $hasLearned = true;
                        break;
                    }
                }
                if (!$hasLearned) {
                    $hasRequiredSkills = false;
                    break;
                }
            }

            if ($hasRequiredSkills) {
                $filteredSkills[] = $skill;
            }
        }

        return $this->render('character/skill_add.html.twig', [
            'character' => $character,
            'skills' => $filteredSkills,
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
        if ($character->isValidated()) {
            $this->addFlash('warning', 'Ce personnage est déjà validé.');
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
        if ($character->isValidated()) {
            $this->addFlash('warning', 'Ce personnage est déjà validé.');
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

            // Vérifier si le personnage a déjà cet équipement
            foreach ($character->getPossessions() as $possession) {
                if ($possession->getGear()->getId() === $gear->getId()) {
                    $this->addFlash('error', 'Vous avez déjà cet équipement.');
                    return $this->redirectToRoute('character_equipment_add', ['id' => $character->getId()]);
                }
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
        if ($character->isValidated()) {
            $this->addFlash('warning', 'Ce personnage est déjà validé.');
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

    #[Route('/{id}/delete', name: 'character_delete', methods: ['GET'])]
    public function delete(Character $character): Response
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce personnage.');
        }

        $this->entityManager->remove($character);
        $this->entityManager->flush();

        $this->addFlash('success', 'Votre personnage a été supprimé avec succès.');
        return $this->redirectToRoute('app_character_index');
    }

    #[Route('/{id}/submit', name: 'character_submit', methods: ['GET'])]
    public function submit(Character $character): Response
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à soumettre ce personnage.');
        }

        // Vérifier que le personnage n'est pas déjà validé
        if ($character->isValidated()) {
            $this->addFlash('warning', 'Ce personnage est déjà validé.');
            return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
        }

        // Vérifier que le personnage a un nom, une faction et une classe
        if (empty($character->getName()) || empty($character->getFaction()) || empty($character->getClass())) {
            $this->addFlash('error', 'Votre personnage doit avoir un nom, une faction et une classe avant d\'être soumis.');
            return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
        }

        $character->setIsValidated(true);
        $this->entityManager->flush();

        $this->addFlash('success', 'Votre personnage a été soumis aux orgas avec succès.');
        return $this->redirectToRoute('app_character_edit', ['id' => $character->getId()]);
    }

    #[Route('/{id}/background', name: 'app_character_background', methods: ['GET', 'POST'])]
    public function background(Request $request, Character $character): Response
    {
        // Vérifier que l'utilisateur est propriétaire du personnage
        if ($character->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce personnage.');
        }

        if ($request->isMethod('POST')) {
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
}
