<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Registration;
use App\Entity\EventType;
use App\Entity\RoleType;
use App\Repository\UserRepository;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class HelloAssoWebhookService
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        /** @var list<string> */
        private array $allowedIps,
        private EmailService $emailService,
        private UserRepository $userRepository,
        private RegistrationRepository $registrationRepository,
        private array $formEventMapping,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    /**
     * Vérifie que la requête webhook provient d'une IP HelloAsso autorisée.
     * Sans compte partenaire, les webhooks ne sont pas signés ; seule la vérification par IP est disponible.
     */
    public function isClientIpAllowed(?string $clientIp): bool
    {
        if ($clientIp === null || $clientIp === '') {
            $this->logger->warning('Webhook HelloAsso : IP client absente');
            return false;
        }

        if (!\in_array($clientIp, $this->allowedIps, true)) {
            $this->logger->warning('Webhook HelloAsso : IP non autorisée', ['ip' => $clientIp]);
            return false;
        }

        return true;
    }

    public function process(array $payload): void
    {
        $eventType = $payload['eventType'] ?? null;
        $data = $payload['data'] ?? null;

        if (!$eventType || !$data) {
            $this->logger->warning('Webhook HelloAsso invalide', $payload);
            return;
        }

        match ($eventType) {
            'Payment' => $this->handlePayment($data),
            'Order'   => $this->handleOrder($data),
            default   => $this->logger->info('Event ignoré', ['event' => $eventType]),
        };
    }

    private function handlePayment(array $payment): void
    {
        $state = $payment['state'] ?? null;
        $orderId = $payment['order']['id'] ?? null;

        if (!$orderId) {
            return;
        }

        match ($state) {
            'Processed' => $this->confirmOrder($payment),
            'Refunded', 'Canceled' => $this->cancelOrder($payment),
            default => null
        };
    }

    private function handleOrder(array $order): void
    {
        // Optionnel : utile si tu veux pré-créer la commande
        $this->logger->info('Order reçu', [
            'orderId' => $order['id'] ?? null
        ]);
    }

    /**
     * Traite un billet (une inscription). Utilisé par le webhook et par l'import CSV.
     * Si le numéro de billet existe déjà en base, la ligne est ignorée (retour null).
     *
     * @param array<string, mixed> $registrationsCreated Inscriptions créées dans cette requête (pour détection doublon intra-requête)
     */
    public function processOneRegistration(
        string $ticketId,
        string $email,
        string $firstName,
        string $lastName,
        ?string $itemName,
        string $eventType,
        array &$registrationsCreated = []
    ): ?Registration {
        $existingRegistrationByTicket = $this->registrationRepository->findOneBy([
            'helloasso_ticket' => $ticketId
        ]);

        if ($existingRegistrationByTicket) {
            $this->logger->info('Inscription déjà existante pour ce ticket', ['itemId' => $ticketId]);
            return null;
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        $userExists = $user !== null;

        if (!$user) {
            $userData = [
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'phone' => ''
            ];
            $user = $this->createUser($userData);
        }

        $existingRegistrationByEvent = $this->registrationRepository->findOneBy([
            'user' => $user,
            'event' => $eventType
        ]);
        if (!$existingRegistrationByEvent) {
            foreach ($registrationsCreated as $created) {
                if ($created->getUser() === $user && $created->getEvent() === $eventType) {
                    $existingRegistrationByEvent = $created;
                    break;
                }
            }
        }

        if ($existingRegistrationByEvent) {
            $this->emailService->sendAdminDuplicateRegistrationAlertEmail(
                $user,
                $existingRegistrationByEvent,
                ['id' => $ticketId, 'name' => $itemName]
            );
            $this->logger->warning('Utilisateur a déjà une inscription pour cet événement', [
                'userId' => $user->getId(),
                'email' => $email,
                'event' => $eventType,
                'existingRegistrationId' => $existingRegistrationByEvent->getId(),
                'newItemId' => $ticketId
            ]);
            return null;
        }

        $registration = $this->createRegistration($user, $eventType, $ticketId, $itemName);
        $this->em->persist($registration);
        $registrationsCreated[] = $registration;

        if ($userExists) {
            $this->emailService->sendRegistrationConfirmationEmail($user, $registration);
        }

        return $registration;
    }

    private function confirmOrder(array $payment): void
    {
        $order = $payment['order'] ?? [];
        $payer = $payment['payer'] ?? [];

        if (empty($order)) {
            $this->logger->warning('Données de commande incomplètes', $payment);
            return;
        }

        $orderId = $order['id'] ?? null;
        if (!$orderId) {
            $this->logger->warning('Order ID manquant', $payment);
            return;
        }

        $items = $payment['items'] ?? $order['items'] ?? [];
        $registrationsCreated = [];

        foreach ($items as $item) {
            if (($item['type'] ?? null) !== 'Payment') {
                continue;
            }

            // exclude certains item's names
            $excludedItemNames = ['Place PNJ'];
            if (in_array($item['name'] ?? '', $excludedItemNames)) {
                continue;
            }

            $itemId = $item['id'] ?? null;
            if (!$itemId) {
                $this->logger->warning('Item ID manquant', ['item' => $item]);
                continue;
            }

            $customFields = $item['customFields'] ?? [];
            $itemEmail = $this->extractEmailFromCustomFields($customFields, $payer);
            if (!$itemEmail) {
                $this->logger->warning('Email manquant pour l\'item', [
                    'itemId' => $itemId,
                    'item' => $item
                ]);
                continue;
            }

            $eventType = $this->getEventTypeFromOrder($order, $item);
            if (!$eventType) {
                $this->logger->warning('Impossible de déterminer l\'événement', [
                    'orderId' => $orderId,
                    'itemId' => $itemId,
                    'item' => $item
                ]);
                continue;
            }

            $this->processOneRegistration(
                (string) $itemId,
                $itemEmail,
                $item['user']['firstName'] ?? $payer['firstName'] ?? '',
                $item['user']['lastName'] ?? $payer['lastName'] ?? '',
                $item['name'] ?? null,
                $eventType,
                $registrationsCreated
            );
        }

        $this->em->flush();

        $this->logger->info('Commande confirmée', [
            'orderId' => $orderId,
            'itemsProcessed' => count($registrationsCreated)
        ]);
    }

    private function cancelOrder(array $payment): void
    {
        $order = $payment['order'] ?? [];
        $items = $payment['items'] ?? $order['items'] ?? [];

        if (empty($items)) {
            $this->logger->warning('Aucun item trouvé pour annulation', $payment);
            return;
        }

        $cancelledCount = 0;

        // Traiter chaque item individuellement
        foreach ($items as $item) {
            if (($item['type'] ?? null) !== 'Registration') {
                continue;
            }

            $itemId = $item['id'] ?? null;
            if (!$itemId) {
                continue;
            }

            $registration = $this->registrationRepository->findOneBy([
                'helloasso_ticket' => (string)$itemId
            ]);

            if ($registration) {
                $this->em->remove($registration);
                $cancelledCount++;
            }
        }

        if ($cancelledCount > 0) {
            $this->em->flush();
            $this->logger->info('Inscriptions annulées', [
                'count' => $cancelledCount
            ]);
        } else {
            $this->logger->warning('Aucune inscription trouvée pour annulation', [
                'items' => array_column($items, 'id')
            ]);
        }
    }

    private function createUser(array $payer): User
    {
        $email = $payer['email'] ?? null;
        $firstName = $payer['firstName'] ?? '';
        $lastName = $payer['lastName'] ?? '';

        if (!$email) {
            throw new \Exception('Email manquant pour la création d\'utilisateur');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstname($firstName);
        $user->setName($lastName);
        $user->setPhone($payer['phone'] ?? '');
        $user->setSocial('');
        $user->setRoles([RoleType::ROLE_USER]);
        // Mot de passe temporaire invalide : l'utilisateur devra utiliser le lien reset-password envoyé par email
        $user->setPassword($this->passwordHasher->hashPassword($user, bin2hex(random_bytes(32))));

        $this->em->persist($user);
        $this->em->flush();

        // Envoyer email d'invitation avec lien reset-password
        $this->emailService->sendWebhookInviteEmail($user);

        $this->logger->info('Utilisateur créé via webhook HelloAsso', [
            'email' => $email
        ]);

        return $user;
    }

    private function createRegistration(User $user, string $eventType, string|int $itemId, ?string $itemName = null): Registration
    {
        $registration = new Registration();
        $registration->setUser($user);
        $registration->setEvent($eventType);
        $registration->setHelloassoTicket((string)$itemId);
        $registration->setItemName($itemName);

        return $registration;
    }

    private function extractEmailFromCustomFields(array $customFields, array $payer): ?string
    {
        // Chercher le customField avec l'ID 6626657 ou le nom "Adresse Mail de Contact"
        foreach ($customFields as $field) {
            $fieldId = $field['id'] ?? null;
            $fieldName = $field['name'] ?? '';
            $answer = $field['answer'] ?? null;

            if (($fieldId === 6626657 || $fieldName === 'Adresse Mail de Contact') && $answer) {
                return $answer;
            }
        }

        // Fallback sur payer.email
        return $payer['email'] ?? null;
    }

    private function getEventTypeFromOrder(array $order, array $item): ?string
    {
        // Essayer d'abord le mapping formulaire → événement
        $formId = $order['formSlug'] ?? $order['form']['id'] ?? null;
        if ($formId && isset($this->formEventMapping[$formId])) {
            return $this->formEventMapping[$formId];
        }

        // Essayer de détecter depuis le nom du billet
        $itemName = $item['name'] ?? '';
        foreach (EventType::cases() as $eventType) {
            $label = $eventType->getLabel();
            if (stripos($itemName, $label) !== false) {
                return $eventType->value;
            }
        }

        // Par défaut, essayer de détecter depuis le nom du formulaire
        $formName = $order['form']['name'] ?? '';
        foreach (EventType::cases() as $eventType) {
            $label = $eventType->getLabel();
            if (stripos($formName, $label) !== false) {
                return $eventType->value;
            }
        }

        return null;
    }
}
