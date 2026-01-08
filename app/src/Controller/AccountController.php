<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\EmergencyContact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Allergy;
use App\Entity\Note;
use App\Entity\Registration;
use App\Entity\EventType;
use App\Repository\RegistrationRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Service\UserService;

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{

    public function __construct(
        private RegistrationRepository $registrationRepository,
        private UserService $userService
    ) {}

    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'breadcrumb' => [
                '/account' => 'Mes informations',
            ],
            'eventTypes' => EventType::getChoices(EventType::STATUS_OPEN),
        ]);
    }

    #[Route('/account/emergency-contact/add', name: 'app_account_emergency_contact_add', methods: ['POST'])]
    public function addEmergencyContact(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['phone'])) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }

        $contact = new EmergencyContact();
        $contact->setName($data['name']);
        $contact->setPhone($data['phone']);
        $contact->setUser($this->getUser());

        $entityManager->persist($contact);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $contact->getId(),
            'name' => $contact->getName(),
            'phone' => $contact->getPhone()
        ]);
    }

    #[Route('/account/emergency-contact/{id}/edit', name: 'app_account_emergency_contact_edit', methods: ['POST'])]
    public function editEmergencyContact(Request $request, EntityManagerInterface $entityManager, EmergencyContact $contact): JsonResponse
    {
        // Vérifier que le contact appartient bien à l'utilisateur connecté
        if ($contact->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Contact non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['phone'])) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }

        $contact->setName($data['name']);
        $contact->setPhone($data['phone']);

        $entityManager->flush();

        return new JsonResponse([
            'id' => $contact->getId(),
            'name' => $contact->getName(),
            'phone' => $contact->getPhone()
        ]);
    }

    #[Route('/account/emergency-contact/{id}/delete', name: 'app_account_emergency_contact_delete', methods: ['POST', 'DELETE'])]
    public function deleteEmergencyContact(EntityManagerInterface $entityManager, EmergencyContact $contact): JsonResponse
    {
        // Vérifier que le contact appartient bien à l'utilisateur connecté
        if ($contact->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Contact non trouvé'], 404);
        }

        $entityManager->remove($contact);
        $entityManager->flush();

        return new JsonResponse(null, 204);
    }

    #[Route('/api/allergy/add', name: 'app_account_allergy_add', methods: ['POST'])]
    public function addAllergy(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        $allergy = new Allergy();
        $allergy->setName($data['name']);
        $allergy->setUser($user);

        $entityManager->persist($allergy);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Allergie ajoutée avec succès']);
    }

    #[Route('/api/allergy/{id}/edit', name: 'app_account_allergy_edit', methods: ['POST'])]
    public function editAllergy(Request $request, Allergy $allergy, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($allergy->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);
        $allergy->setName($data['name']);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Allergie modifiée avec succès']);
    }

    #[Route('/api/allergy/{id}/delete', name: 'app_account_allergy_delete', methods: ['POST', 'DELETE'])]
    public function deleteAllergy(Allergy $allergy, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($allergy->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($allergy);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Allergie supprimée avec succès']);
    }

    #[Route('/api/note/add', name: 'app_account_note_add', methods: ['POST'])]
    public function addNote(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        $note = new Note();
        $note->setTitle($data['title']);
        $note->setContent($data['content']);
        $note->setUser($user);

        $entityManager->persist($note);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Note ajoutée avec succès']);
    }

    #[Route('/api/note/{id}/edit', name: 'app_account_note_edit', methods: ['POST'])]
    public function editNote(Request $request, Note $note, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($note->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $data = json_decode($request->getContent(), true);
        $note->setTitle($data['title']);
        $note->setContent($data['content']);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Note modifiée avec succès']);
    }

    #[Route('/api/note/{id}/delete', name: 'app_account_note_delete', methods: ['POST', 'DELETE'])]
    public function deleteNote(Note $note, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($note->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($note);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Note supprimée avec succès']);
    }

    #[Route('/account/registration/add', name: 'app_account_registration_add', methods: ['POST'])]
    public function addRegistration(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['event']) || !isset($data['ticket'])) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        try {
            if ($this->getUser()->isOrga() && $this->getUser()->getId() != $data['userId']) {
                $user = $entityManager->getRepository(User::class)->find($data['userId']);
            } else {
                $user = $this->getUser();
            }
            // check if the event is valid
            if (!in_array($data['event'], EventType::getChoices())) {
                return new JsonResponse(['error' => 'Événement invalide'], 400);
            }
            // check if the ticket is valid
            // TODO helloasso api

            // check if the event is already registered
            $existingRegistration = $this->registrationRepository->findOneBy([
                'user' => $user,
                'event' => $data['event']
            ]);

            if ($existingRegistration) {
                return new JsonResponse(['error' => 'Événement déjà enregistré'], 400);
            }

            $registration = new Registration();
            $registration->setUser($user);
            $registration->setEvent($data['event']);
            $registration->setHelloassoTicket($data['ticket']);

            $entityManager->persist($registration);
            $entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue lors de la création de l\'inscription'], 500);
        }
    }

    #[Route('/account/registration/{id}/edit', name: 'app_account_registration_edit', methods: ['POST'])]
    public function editRegistration(Request $request, Registration $registration, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['event']) || !isset($data['ticket'])) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        try {
            $registration->setEvent($data['event']);
            $registration->setHelloassoTicket($data['ticket']);
            $entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue lors de la modification de l\'inscription'], 500);
        }
    }

    #[Route('/account/profile/edit', name: 'app_account_profile_edit', methods: ['POST'])]
    public function editProfile(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->getUser();
            /** @var User $user */
            $this->userService->updateProfile($user, $data);
            return new JsonResponse(['message' => 'Profil modifié avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/account/login/edit', name: 'app_account_login_edit', methods: ['POST'])]
    public function editLoginInfo(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->getUser();
            /** @var User $user */
            $this->userService->updateLoginInfo($user, $data, $passwordHasher);
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
