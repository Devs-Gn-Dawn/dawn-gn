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
}
