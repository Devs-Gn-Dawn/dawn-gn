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

        return $this->render('orga/characters.html.twig', [
            'title' => 'Personnages en cours de validation',
            'breadcrumb' => [
                '/orga' => 'Organisation',
                '/orga/characters' => 'Gestion des personnages',
            ],
            'characters' => $this->entityManager->getRepository(Character::class)->findBy(['validationType' => ValidationType::EN_COURS])
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
            'eventTypes' => EventType::getChoices(),
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

    private function sendEmail(User $user, string $message, string $title, MailerInterface $mailer): void
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

        $mailer->send($email);
    }

    #[Route('/api/contact_player', name: 'api_contact_player', methods: ['POST'])]
    public function contactPlayer(Request $request, MailerInterface $mailer): JsonResponse
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

            $this->sendEmail($user, $data['message'], $data['title'], $mailer);

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/create_character', name: 'app_orga_character_create', methods: ['POST'])]
    public function createCharacter(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $data = $request->getContent() ? json_decode($request->getContent(), true) : $request->request->all();

        if (empty($data['character_name']) || empty($data['faction']) || empty($data['class'])) {
            return $this->json(['error' => 'Tous les champs sont obligatoires'], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->find($data['userId']);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 400);
        }

        $character = new Character();
        $character->setUser($user);
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

        return $this->json(['success' => true]);
    }

    #[Route('/api/character/asset/{characterAssetId}/delete', name: 'api_character_asset_delete', methods: ['POST'])]
    public function deleteAsset(CharacterAsset $characterAsset): JsonResponse
    {
        $this->entityManager->remove($characterAsset);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }
}
