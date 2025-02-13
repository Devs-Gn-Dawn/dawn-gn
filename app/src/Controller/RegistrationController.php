<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $name = $request->request->get('name');

            // Vérification basique des données
            if ($email && $password && $name) {
                $user->setEmail($email);
                $user->setName($name);

                // Hashage du mot de passe
                $hashedPassword = $userPasswordHasher->hashPassword(
                    $user,
                    $password
                );
                $user->setPassword($hashedPassword);

                // Sauvegarde en base de données
                $entityManager->persist($user);
                $entityManager->flush();

                // Redirection vers la page de connexion
                $this->addFlash('success', 'Votre compte a été créé avec succès !');
                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('error', 'Veuillez remplir tous les champs.');
        }

        return $this->render('registration/register.html.twig');
    }
}
