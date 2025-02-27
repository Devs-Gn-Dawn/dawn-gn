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

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('account/index.html.twig', [
            'user' => $user,
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
}
