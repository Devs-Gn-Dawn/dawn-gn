<?php

namespace App\Controller;

use App\Entity\RoleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted(RoleType::ROLE_ADMIN)]
class AdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'breadcrumb' => [
                '/admin' => 'Administration',
            ],
        ]);
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function users(Request $request): Response
    {
        $role = $request->getSession()->get('user_filter_role', null);

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u')
            ->from(User::class, 'u');

        if ($role) {
            $qb->where('u.roles LIKE :role')
                ->setParameter('role', '%' . $role . '%');
        }

        $users = $qb->getQuery()->getResult();

        return $this->render('admin/users.html.twig', [
            'breadcrumb' => [
                '/admin' => 'Administration',
                '/admin/users' => 'Gestion des utilisateurs',
            ],
            'users' => $users,
            'currentFilter' => [
                'role' => $role
            ]
        ]);
    }

    #[Route('/api/user/create', name: 'api_user_create', methods: ['POST'])]
    public function createUser(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['success' => false, 'error' => 'Données invalides'], 400);
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setFirstname($data['firstname']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);
        $user->setRoles($data['roles']);

        // Hashage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/user/{id}/update', name: 'api_user_update', methods: ['POST'])]
    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Utilisateur non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['success' => false, 'error' => 'Données invalides'], 400);
        }

        $user->setName($data['name']);
        $user->setFirstname($data['firstname']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);
        $user->setRoles($data['roles']);

        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/user/{id}/delete', name: 'api_user_delete', methods: ['POST', 'DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Utilisateur non trouvé'], 404);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/user/filter', name: 'api_user_filter', methods: ['POST'])]
    public function filterUsers(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $role = $data['role'] ?? null;

        // Sauvegarder les filtres en session
        $request->getSession()->set('user_filter_role', $role);

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u')
            ->from(User::class, 'u');

        if ($role) {
            $qb->where('u.roles LIKE :role')
                ->setParameter('role', '%' . $role . '%');
        }

        $users = $qb->getQuery()->getResult();

        $usersData = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'firstname' => $user->getFirstname(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'roles' => $user->getRoles(),
                'charactersCount' => count($user->getCharacters()),
                'registrationsCount' => count($user->getRegistrations())
            ];
        }, $users);

        return new JsonResponse([
            'success' => true,
            'users' => $usersData
        ]);
    }
}
