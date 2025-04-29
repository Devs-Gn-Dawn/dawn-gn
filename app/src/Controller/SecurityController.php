<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\User;

class SecurityController extends AbstractController
{
    public function __construct(private MailerInterface $mailer) {}

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'user' => false,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide - elle sera interceptée par la configuration de sécurité
    }

    #[Route('/api/contact', name: 'app_account_contact', methods: ['POST'])]
    public function contact(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
        /**
         * @var User $user
         */

        if (empty($data['subject'] ?? null) || empty($data['message'] ?? null)) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }

        $userMainFaction = '';

        if (!$user instanceof User) {
            $emailFrom = $data['email'] ?? null;
        } else {
            $emailFrom = $user->getEmail();
            if ($user->hasMainCharacter() && ($data['generic'] ?? 0) == 0) {
                $userMainFaction = $user->getMainCharacter()->getFaction();
            }
        }

        if (!$emailFrom) {
            return new JsonResponse(['error' => 'Email manquant'], 400);
        } elseif (!filter_var($emailFrom, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Email invalide'], 400);
        }

        $orgaEmail = \App\Entity\OrgasType::getEmail($userMainFaction);

        try {
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@dawn-gn.com', 'Dawn GN'))
                ->to($orgaEmail)
                ->replyTo($emailFrom)
                ->subject('[Dawn GN] - ' . $data['subject'])
                ->htmlTemplate('contact/email.html.twig')
                ->context([
                    'message' => $data['message'],
                    'user' => $user,
                    'replyTo' => $emailFrom,
                ]);

            $this->mailer->send($email);

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur est survenue lors de l\'envoi du message : ' . $e->getMessage()], 500);
        }
    }
}
