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
            'breadcrumb' => [
                '/orga' => 'Organisation',
                '/orga/characters' => 'Gestion des personnages',
            ],
            'characters' => $this->entityManager->getRepository(Character::class)->findBy(['validationType' => ValidationType::EN_COURS])
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

    private function sendEmail(Character $character, string $message, string $title, MailerInterface $mailer): void
    {
        $user = $character->getUser();
        if (!$user) {
            throw new \Exception('Utilisateur non trouvé');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@dawn-gn.com', 'Dawn GN'))
            ->to($user->getEmail())
            ->subject('[Dawn GN] - ' . $title)
            ->htmlTemplate('contact/orga_email.html.twig')
            ->context([
                'message' => $message,
                'title' => $title,
                'character' => $character,
            ]);

        $mailer->send($email);
    }

    #[Route('/api/contact_player', name: 'api_contact_player', methods: ['POST'])]
    public function contactPlayer(Request $request, MailerInterface $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            if (empty($data['characterId'] ?? null) || empty($data['message'] ?? null) || empty($data['title'] ?? null)) {
                throw new \Exception('Données manquantes');
            }

            $character = $this->entityManager->getRepository(Character::class)->find($data['characterId']);
            if (!$character) {
                throw new \Exception('Personnage non trouvé');
            }

            $user = $character->getUser();
            if (!$user) {
                throw new \Exception('Utilisateur non trouvé');
            }

            $this->sendEmail($character, $data['message'], $data['title'], $mailer);

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
