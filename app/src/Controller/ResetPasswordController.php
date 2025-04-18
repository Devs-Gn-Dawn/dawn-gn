<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Psr\Log\LoggerInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    #[Route('', name: 'app_forgot_password_request', methods: ['GET', 'POST'])]
    public function request(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            if (empty($email)) {
                $this->addFlash('reset_password_error', 'Veuillez entrer votre adresse email.');
                return $this->redirectToRoute('app_forgot_password_request');
            }

            $this->processSendingPasswordResetEmail($email);
        }

        return $this->render('security/reset_password/request.html.twig');
    }

    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $this->logger->info('Reset token in session:', [
            'token' => $resetToken,
            'expirationMessageKey' => $resetToken->getExpirationMessageKey(),
            'expirationMessageData' => $resetToken->getExpirationMessageData(),
        ]);

        return $this->render('security/reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    #[Route('/reset/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, string $token = null): Response
    {
        if ($token) {
            $this->storeTokenInSession($token);
            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('Aucun token de réinitialisation trouvé dans l\'URL ou la session.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE,
                $e->getReason()
            ));
            return $this->redirectToRoute('app_forgot_password_request');
        }

        if ($request->isMethod('POST')) {
            $plainPassword = $request->request->get('plainPassword');
            $confirmPassword = $request->request->get('confirmPassword');

            if (empty($plainPassword) || empty($confirmPassword)) {
                $this->addFlash('reset_password_error', 'Veuillez remplir tous les champs.');
                return $this->redirectToRoute('app_reset_password');
            }

            if ($plainPassword !== $confirmPassword) {
                $this->addFlash('reset_password_error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_reset_password');
            }

            if (strlen($plainPassword) < 6) {
                $this->addFlash('reset_password_error', 'Le mot de passe doit contenir au moins 6 caractères.');
                return $this->redirectToRoute('app_reset_password');
            }

            $this->resetPasswordHelper->removeResetRequest($token);

            $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            $this->cleanSessionAfterReset();
            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password/reset.html.twig');
    }

    private function processSendingPasswordResetEmail(string $emailFormData): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        if (!$user) {
            $this->addFlash('success', 'Un email de réinitialisation a été envoyé à votre adresse email si un compte existe avec cette adresse. Le lien expirera dans 1 heure.');
            return;
            // return $this->redirectToRoute('app_forgot_password_request');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);

            $this->logger->info('Reset token in session:', [
                'token' => $resetToken,
                'url' => $this->generateUrl('app_reset_password', ['token' => $resetToken->getToken()]),
            ]);
            $email = (new TemplatedEmail())
                ->from(new Address('noreply@dawn-gn.fr', 'Dawn GN'))
                ->to($user->getEmail())
                ->subject('Votre demande de réinitialisation de mot de passe')
                ->htmlTemplate('security/reset_password/email.html.twig')
                ->context([
                    'resetToken' => $resetToken,
                ]);

            // TODO: Envoyer l'email

            $this->setTokenObjectInSession($resetToken);
            $this->addFlash('success', 'Un email de réinitialisation a été envoyé à votre adresse email si un compte existe avec cette adresse. Le lien expirera dans ' . $resetToken->getExpiresAtDiffForHumans());
        } catch (ResetPasswordExceptionInterface $e) {
            if ($e instanceof TooManyPasswordRequestsException) {
                $this->addFlash('reset_password_error', 'Vous avez déjà demandé une réinitialisation de mot de passe. Veuillez vérifier votre email ou vos spam. Vous pouvez réessayer dans ' . round($e->getRetryAfter() / 60) . ' minutes.');
            } else {
                $this->addFlash('reset_password_error', 'Une erreur est survenue lors de la génération du token de réinitialisation. ' . $e->getReason());
            }
            // return $this->redirectToRoute('app_forgot_password_request');
        }



        // return $this->redirectToRoute('app_check_email');

    }
}
