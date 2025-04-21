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

#[IsGranted(RoleType::ROLE_ORGA)]
class OrgaController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
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
        // Récupérer les filtres de la session
        $faction = $request->getSession()->get('character_filter_faction', null);
        $class = $request->getSession()->get('character_filter_class', null);

        // Créer la requête de base
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c')
            ->from(Character::class, 'c');

        // Appliquer les filtres
        if ($faction) {
            $qb->andWhere('c.faction = :faction')
                ->setParameter('faction', $faction);
        }
        if ($class) {
            $qb->andWhere('c.class = :class')
                ->setParameter('class', $class);
        }

        return $this->render('orga/characters.html.twig', [
            'breadcrumb' => [
                '/orga' => 'Organisation',
                '/orga/characters' => 'Gestion des personnages',
            ],
            'characters' => $qb->getQuery()->getResult(),
            'factions' => FactionType::getChoices(),
            'currentFilter' => [
                'faction' => $faction,
                'class' => $class
            ]
        ]);
    }

    #[Route('/api/character/{id}/validate', name: 'api_character_validate', methods: ['POST'])]
    public function validateCharacter(Character $character): JsonResponse
    {
        try {
            $character->setValidationType(ValidationType::VALIDE);
            $this->entityManager->flush();

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Une erreur est survenue lors de la validation du personnage.'], 500);
        }
    }

    #[Route('/api/character/{id}/reject', name: 'api_character_reject', methods: ['POST'])]
    public function rejectCharacter(Character $character): JsonResponse
    {
        try {
            $character->setValidationType(ValidationType::REJETE);
            $this->entityManager->flush();

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Une erreur est survenue lors du rejet du personnage.'], 500);
        }
    }

    #[Route('/api/character/filter', name: 'api_character_filter', methods: ['POST'])]
    public function filterCharacters(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $faction = $data['faction'] ?? null;
        $class = $data['class'] ?? null;

        // Sauvegarder les filtres en session
        $request->getSession()->set('character_filter_faction', $faction);

        // Créer la requête
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c')
            ->from(Character::class, 'c');

        // Appliquer les filtres
        if ($faction) {
            $qb->andWhere('c.faction = :faction')
                ->setParameter('faction', $faction);
        }
        if ($class) {
            $qb->andWhere('c.class = :class')
                ->setParameter('class', $class);
        }

        $characters = $qb->getQuery()->getResult();

        // Transformer les données pour la réponse JSON
        $charactersData = array_map(function ($character) {
            return [
                'id' => $character->getId(),
                'name' => $character->getName(),
                'faction' => $character->getFaction(),
                'class' => $character->getClass(),
                'validationType' => $character->getValidationType(),
                'user' => [
                    'fullName' => $character->getUser()->getFullName(),
                    'email' => $character->getUser()->getEmail()
                ]
            ];
        }, $characters);

        return $this->json([
            'success' => true,
            'characters' => $charactersData
        ]);
    }

    #[Route('/orga/character/{id}/edit', name: 'app_orga_character_edit')]
    public function editCharacter(Character $character): Response
    {
        return $this->render('orga/character_edit.html.twig', [
            'character' => $character
        ]);
    }
}
