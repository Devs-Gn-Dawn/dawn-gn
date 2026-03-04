<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Character;
use App\Entity\Registration;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class EmailService
{

    private const SENDER_EMAIL = 'no-reply@dawn-gn.com';
    private const SENDER_NAME = 'Dawn GN';
    private Address $senderAddress;

    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository
    ) {
        $this->senderAddress = new Address(self::SENDER_EMAIL, self::SENDER_NAME);
    }

    /**
     * Envoie un email d'invitation à un utilisateur
     */
    public function sendInviteEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from($this->senderAddress)
            ->to($user->getEmail())
            ->subject('[Dawn GN] - Votre personnage')
            ->htmlTemplate('contact/invit.html.twig')
            ->context([
                'user' => $user
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie un email d'invitation pour un utilisateur créé via webhook HelloAsso
     */
    public function sendWebhookInviteEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from($this->senderAddress)
            ->to($user->getEmail())
            ->subject('[Dawn GN] - Votre compte a été créé')
            ->htmlTemplate('email/webhook_invitation.html.twig')
            ->context([
                'user' => $user
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de contact depuis l'organisation vers un joueur
     */
    public function sendContactEmail(User $to, string $subject, string $message, ?User $from = null): void
    {
        $email = (new TemplatedEmail())
            ->from($this->senderAddress)
            ->to($to->getEmail())
            ->replyTo($from?->getOrgaEmail() ?? self::SENDER_EMAIL)
            ->subject('[Dawn GN] - ' . $subject)
            ->htmlTemplate('contact/orga_email.html.twig')
            ->context([
                'message' => $message,
                'title' => $subject,
                'user' => $to,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de contact depuis un utilisateur vers l'organisation
     */
    public function sendContactToOrga(string $toEmail, string $subject, string $message, ?string $replyToEmail = null, ?User $user = null): void
    {
        $email = (new TemplatedEmail())
            ->from($this->senderAddress)
            ->to($toEmail)
            ->replyTo($replyToEmail ?? self::SENDER_EMAIL)
            ->subject('[Dawn GN] - ' . $subject)
            ->htmlTemplate('contact/email.html.twig')
            ->context([
                'message' => $message,
                'user' => $user,
                'replyTo' => $replyToEmail,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de validation/rejet de personnage
     */
    public function sendCharacterValidationEmail(Character $character, string $status): void
    {
        $user = $character->getUser();
        $characterUrl = $this->urlGenerator->generate(
            'app_character_edit',
            ['id' => $character->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $title = $status === 'validated' ? 'Personnage validé' : 'Personnage non validé';
        $message = $status === 'validated'
            ? 'Votre personnage a été validé. Vous pouvez le retrouver à l\'adresse suivante : ' . $characterUrl
            : 'Votre personnage n\'a pas été validé. Vous pouvez le retrouver à l\'adresse suivante : ' . $characterUrl;

        $this->sendContactEmail($user, $title, $message);
    }

    /**
     * Envoie un email de réinitialisation de mot de passe
     */
    public function sendPasswordResetEmail(User $user, ResetPasswordToken $token, string $expiresAtDiffForHumans): void
    {
        $email = (new TemplatedEmail())
            ->from($this->senderAddress)
            ->to($user->getEmail())
            ->subject('[Dawn GN] - Votre demande de réinitialisation de mot de passe')
            ->htmlTemplate('security/reset_password/email.html.twig')
            ->context([
                'resetToken' => $token,
                'expiresAtDiffForHumans' => $expiresAtDiffForHumans,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de confirmation d'inscription à un utilisateur existant
     */
    public function sendRegistrationConfirmationEmail(User $user, Registration $registration): void
    {
        $email = (new TemplatedEmail())
            ->from($this->senderAddress)
            ->to($user->getEmail())
            ->subject('[Dawn GN] - Confirmation d\'inscription')
            ->htmlTemplate('email/registration_confirmation.html.twig')
            ->context([
                'user' => $user,
                'registration' => $registration,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie un email d'alerte aux administrateurs pour une commande avec quantité >1
     */
    public function sendAdminQuantityAlertEmail(array $orderData, int $quantity): void
    {
        $admins = $this->userRepository->findAdmins();

        if (empty($admins)) {
            return;
        }

        foreach ($admins as $admin) {
            $email = (new TemplatedEmail())
                ->from($this->senderAddress)
                ->to($admin->getEmail())
                ->subject('[Dawn GN] - Alerte : Commande avec quantité >1')
                ->htmlTemplate('email/admin_quantity_alert.html.twig')
                ->context([
                    'orderData' => $orderData,
                    'quantity' => $quantity,
                ]);

            $this->mailer->send($email);
        }
    }

    /**
     * Envoie un email d'alerte aux administrateurs si un utilisateur a déjà une inscription pour le même événement
     */
    public function sendAdminDuplicateRegistrationAlertEmail(User $user, Registration $existingRegistration, array $newItemData): void
    {
        $admins = $this->userRepository->findAdmins();

        if (empty($admins)) {
            return;
        }

        foreach ($admins as $admin) {
            $email = (new TemplatedEmail())
                ->from($this->senderAddress)
                ->to($admin->getEmail())
                ->subject('[Dawn GN] - Alerte : Inscription en doublon pour le même événement')
                ->htmlTemplate('email/admin_duplicate_registration_alert.html.twig')
                ->context([
                    'user' => $user,
                    'existingRegistration' => $existingRegistration,
                    'newItemData' => $newItemData,
                ]);

            $this->mailer->send($email);
        }
    }
}
