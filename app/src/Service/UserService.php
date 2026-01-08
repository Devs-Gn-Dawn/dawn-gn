<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\FactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Met à jour le profil d'un utilisateur
     */
    public function updateProfile(User $user, array $data): void
    {
        if (!isset($data['name']) || !isset($data['firstname']) || !isset($data['phone']) || !isset($data['droitImage'])) {
            throw new \Exception('Données manquantes');
        }

        $user->setName($data['name']);
        $user->setFirstname($data['firstname']);
        $user->setPhone($data['phone']);
        $user->setDroitImage($data['droitImage']);

        $this->entityManager->flush();
    }

    /**
     * Met à jour les informations de connexion d'un utilisateur
     */
    public function updateLoginInfo(User $user, array $data, UserPasswordHasherInterface $passwordHasher): void
    {
        if (!$user instanceof User) {
            throw new \Exception('Utilisateur non trouvé');
        }

        if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                throw new \Exception('Cet email est déjà utilisé');
            }
            $user->setEmail($data['email']);
        }

        if (!empty($data['password'])) {
            if ($data['password'] !== $data['password_confirm']) {
                throw new \Exception('Les mots de passe ne correspondent pas');
            }
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        $this->entityManager->flush();
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function createUser(array $data, UserPasswordHasherInterface $passwordHasher): User
    {
        if (!$data) {
            throw new \Exception('Données invalides');
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setFirstname($data['firstname']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);
        $user->setRoles($data['roles']);
        $user->setSocial('');
        $user->setFaction(empty($data['faction']) ? null : FactionType::from($data['faction']));

        // Hashage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Met à jour un utilisateur
     */
    public function updateUser(User $user, array $data): void
    {
        if (!$data) {
            throw new \Exception('Données invalides');
        }

        $user->setName($data['name']);
        $user->setFirstname($data['firstname']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);
        $user->setRoles($data['roles']);
        $user->setFaction(empty($data['faction']) ? null : FactionType::from($data['faction']));

        $this->entityManager->flush();
    }

    /**
     * Filtre les utilisateurs par rôle
     */
    public function filterUsers(?string $role = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u')
            ->from(User::class, 'u');

        if ($role) {
            $qb->where('u.roles LIKE :role')
                ->setParameter('role', '%' . $role . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
