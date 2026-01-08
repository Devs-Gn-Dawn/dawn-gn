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

class HelloAssoWebhookService
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private string $webhookSecret,
        private EmailService $emailService,
        private UserRepository $userRepository,
        private RegistrationRepository $registrationRepository,
        private array $formEventMapping
    ) {}

    public function isSignatureValid(string $rawBody, ?string $signature): bool
    {
        if (!$signature) {
            $this->logger->warning('Signature HelloAsso absente');
            return false;
        }

        $computedSignature = hash_hmac(
            'sha256',
            $rawBody,
            $this->webhookSecret
        );

        return hash_equals($computedSignature, $signature);
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

    private function confirmOrder(array $payment): void
    {
        $order = $payment['order'] ?? [];
        $payer = $payment['payer'] ?? [];

        if (empty($order) || empty($payer)) {
            $this->logger->warning('Données de commande incomplètes', $payment);
            return;
        }

        $orderId = $order['id'] ?? null;
        $payerEmail = $payer['email'] ?? null;

        if (!$orderId || !$payerEmail) {
            $this->logger->warning('Order ID ou email manquant', $payment);
            return;
        }

        // Vérifier si une inscription existe déjà avec ce ticket
        $existingRegistration = $this->registrationRepository->findOneBy([
            'helloasso_ticket' => $orderId
        ]);

        if ($existingRegistration) {
            $this->logger->info('Inscription déjà existante pour cette commande', [
                'orderId' => $orderId,
                'email' => $payerEmail
            ]);
            return;
        }

        // Récupérer ou créer l'utilisateur
        $user = $this->userRepository->findOneBy(['email' => $payerEmail]);
        $userExists = $user !== null;

        if (!$user) {
            $user = $this->createUser($payer);
        }

        // Traiter chaque item de la commande
        $items = $order['items'] ?? [];
        $registrationsCreated = [];
        
        foreach ($items as $item) {
            if (($item['type'] ?? null) !== 'Ticket') {
                continue;
            }

            $quantity = (int)($item['quantity'] ?? 1);
            $eventType = $this->getEventTypeFromOrder($order, $item);

            if (!$eventType) {
                $this->logger->warning('Impossible de déterminer l\'événement', [
                    'orderId' => $orderId,
                    'item' => $item
                ]);
                continue;
            }

            if ($quantity > 1) {
                // Créer une seule inscription
                $registration = $this->createRegistration($user, $eventType, $orderId);
                $this->em->persist($registration);
                $registrationsCreated[] = $registration;

                // Logger l'alerte
                $this->logger->warning('Commande avec quantité >1', [
                    'orderId' => $orderId,
                    'quantity' => $quantity,
                    'email' => $payerEmail,
                    'event' => $eventType
                ]);

                // Envoyer alerte aux admins
                $this->emailService->sendAdminQuantityAlertEmail($payment, $quantity);
            } else {
                // Création normale de l'inscription
                $registration = $this->createRegistration($user, $eventType, $orderId);
                $this->em->persist($registration);
                $registrationsCreated[] = $registration;
            }
        }

        $this->em->flush();

        // Si l'utilisateur existait déjà, envoyer email de confirmation pour la première inscription créée
        if ($userExists && !empty($registrationsCreated)) {
            $this->emailService->sendRegistrationConfirmationEmail($user, $registrationsCreated[0]);
        }

        $this->logger->info('Commande confirmée', [
            'orderId' => $orderId,
            'email' => $payerEmail,
            'userExists' => $userExists
        ]);
    }

    private function cancelOrder(array $payment): void
    {
        $orderId = $payment['order']['id'] ?? null;

        if (!$orderId) {
            return;
        }

        $registration = $this->registrationRepository->findOneBy([
            'helloasso_ticket' => $orderId
        ]);

        if ($registration) {
            $this->em->remove($registration);
            $this->em->flush();

            $this->logger->info('Commande annulée', [
                'orderId' => $orderId
            ]);
        } else {
            $this->logger->warning('Inscription non trouvée pour annulation', [
                'orderId' => $orderId
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
        $user->setRoles([RoleType::ROLE_USER]);
        // Pas de mot de passe défini - l'utilisateur devra utiliser reset-password

        $this->em->persist($user);
        $this->em->flush();

        // Envoyer email d'invitation avec lien reset-password
        $this->emailService->sendWebhookInviteEmail($user);

        $this->logger->info('Utilisateur créé via webhook HelloAsso', [
            'email' => $email
        ]);

        return $user;
    }

    private function createRegistration(User $user, string $eventType, string $orderId): Registration
    {
        $registration = new Registration();
        $registration->setUser($user);
        $registration->setEvent($eventType);
        $registration->setHelloassoTicket($orderId);

        return $registration;
    }

    private function getEventTypeFromOrder(array $order, array $item): ?string
    {
        // Essayer d'abord le mapping formulaire → événement
        $formId = $order['form']['id'] ?? null;
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
