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
            $firstname = $request->request->get('firstname');

            // Vérification basique des données
            if (empty($email) || empty($password) || empty($name) || empty($firstname)) {
                $this->addFlash('error', 'Veuillez remplir tous les champs.');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Veuillez entrer une adresse email valide.');
            } elseif ($entityManager->getRepository(User::class)->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Cette adresse email est déjà utilisée.');
            } else {
                $user->setEmail($email);
                $user->setName($name);
                $user->setFirstname($firstname);
                $user->setPhone('');
                $user->setSocial('');

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
        }

        return $this->render('registration/register.html.twig');
    }
}
