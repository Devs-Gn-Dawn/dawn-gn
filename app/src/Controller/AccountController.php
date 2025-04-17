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

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    public function __construct(
        private RegistrationRepository $registrationRepository
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

    #[Route('/account/emergency-contact/{id}/delete', name: 'app_account_emergency_contact_delete', methods: ['DELETE'])]
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

    #[Route('/allergy/add', name: 'app_account_allergy_add', methods: ['POST'])]
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

    #[Route('/allergy/{id}/edit', name: 'app_account_allergy_edit', methods: ['POST'])]
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

    #[Route('/allergy/{id}/delete', name: 'app_account_allergy_delete', methods: ['DELETE'])]
    public function deleteAllergy(Allergy $allergy, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($allergy->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($allergy);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Allergie supprimée avec succès']);
    }

    #[Route('/note/add', name: 'app_account_note_add', methods: ['POST'])]
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

    #[Route('/note/{id}/edit', name: 'app_account_note_edit', methods: ['POST'])]
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

    #[Route('/note/{id}/delete', name: 'app_account_note_delete', methods: ['DELETE'])]
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
            // check if the event is valid
            if (!in_array($data['event'], EventType::getChoices())) {
                return new JsonResponse(['error' => 'Événement invalide'], 400);
            }
            // check if the ticket is valid
            // TODO helloasso api

            // check if the event is already registered
            $existingRegistration = $this->registrationRepository->findOneBy([
                'user' => $this->getUser(),
                'event' => $data['event']
            ]);

            if ($existingRegistration) {
                return new JsonResponse(['error' => 'Événement déjà enregistré'], 400);
            }

            $registration = new Registration();
            $registration->setUser($this->getUser());
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
    public function editProfile(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
        /**
         * @var User $user
         */

        if (!isset($data['name']) || !isset($data['firstname']) || !isset($data['phone']) || !isset($data['social'])) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }

        $user->setName($data['name']);
        $user->setFirstname($data['firstname']);
        $user->setPhone($data['phone']);
        $user->setSocial($data['social']);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Profil modifié avec succès']);
    }

    #[Route('/account/login/edit', name: 'app_account_login_edit', methods: ['POST'])]
    public function editLoginInfo(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return new JsonResponse(['error' => 'Cet email est déjà utilisé'], 400);
            }
            $user->setEmail($data['email']);
        }

        if (!empty($data['password'])) {
            if ($data['password'] !== $data['password_confirm']) {
                return new JsonResponse(['error' => 'Les mots de passe ne correspondent pas'], 400);
            }
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
