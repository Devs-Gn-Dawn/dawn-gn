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
use App\Entity\EventType;
use App\Entity\FactionType;
use App\Entity\Registration;
use App\Repository\RegistrationRepository;
use App\Service\EmailService;
use App\Service\HelloAssoCsvImportService;
use App\Service\UserService;

#[IsGranted(RoleType::ROLE_ADMIN)]
class AdminController extends AbstractController
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        private EmailService $emailService,
        private UserService $userService,
        private HelloAssoCsvImportService $csvImportService,
        private RegistrationRepository $registrationRepository
    ) {
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
            'factions' => FactionType::getChoices(),
            'users' => $users,
            'currentFilter' => [
                'role' => $role
            ]
        ]);
    }

    #[Route('/api/user/create', name: 'api_user_create', methods: ['POST'])]
    public function createUser(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $this->userService->createUser($data, $passwordHasher);
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/user/{id}/update', name: 'api_user_update', methods: ['POST'])]
    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Utilisateur non trouvé'], 404);
        }

        try {
            $data = json_decode($request->getContent(), true);
            $this->userService->updateUser($user, $data);
            return new JsonResponse(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
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

        $users = $this->userService->filterUsers($role);

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

    #[Route('/admin/send-invite', name: 'admin_send_invite')]
    public function invite(): Response
    {
        return $this->render('admin/send_invite.html.twig', [
            'breadcrumb' => [
                '/admin' => 'Administration',
                '/admin/send-invite' => 'Envoyer un mail d\'invitation',
            ],
        ]);
    }

    #[Route('/admin/add-participation', name: 'app_admin_add_participation', methods: ['GET', 'POST'])]
    public function addParticipation(Request $request): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findBy([], ['name' => 'ASC', 'firstname' => 'ASC']);
        $eventChoices = EventType::getChoices();
        $selectedUserId = $request->query->getInt('user', 0);
        $existingEvents = [];

        if ($selectedUserId > 0) {
            $selectedUser = $this->entityManager->getRepository(User::class)->find($selectedUserId);
            if ($selectedUser) {
                $regs = $this->registrationRepository->findBy(['user' => $selectedUser]);
                $existingEvents = array_values(array_unique(array_filter(array_map(
                    static fn (Registration $r) => $r->getEvent(),
                    $regs
                ))));
            }
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('admin_add_participation', $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');
            } else {
                $userId = $request->request->getInt('user_id');
                $events = $request->request->all('events');

                if (!$userId) {
                    $this->addFlash('error', 'Veuillez sélectionner un joueur.');
                } elseif (empty($events)) {
                    $this->addFlash('error', 'Veuillez sélectionner au moins un événement.');
                } else {
                    $user = $this->entityManager->getRepository(User::class)->find($userId);
                    if (!$user) {
                        $this->addFlash('error', 'Joueur non trouvé.');
                    } else {
                        $validEvents = array_filter($events, fn ($e) => \in_array($e, EventType::EVENTS, true));
                        $added = 0;
                        foreach ($validEvents as $event) {
                            $existing = $this->registrationRepository->findOneBy([
                                'user' => $user,
                                'event' => $event,
                            ]);
                            if (!$existing) {
                                $registration = new Registration();
                                $registration->setUser($user);
                                $registration->setEvent($event);
                                $registration->setHelloassoTicket('admin-manual-' . uniqid('', true));
                                $this->entityManager->persist($registration);
                                $added++;
                            }
                        }
                        $this->entityManager->flush();
                        $this->addFlash('success', $added === 0
                            ? 'Aucune nouvelle participation ajoutée (toutes existaient déjà).'
                            : sprintf('%d participation(s) ajoutée(s).', $added));
                        return $this->redirectToRoute('app_admin_add_participation', ['user' => $userId]);
                    }
                }
            }
        }

        return $this->render('admin/add_participation.html.twig', [
            'breadcrumb' => [
                '/admin' => 'Administration',
                '/admin/add-participation' => 'Ajouter des participations',
            ],
            'users' => $users,
            'eventChoices' => $eventChoices,
            'selectedUserId' => $selectedUserId,
            'existingEvents' => $existingEvents,
        ]);
    }

    #[Route('/admin/add-participation/existing-events', name: 'app_admin_add_participation_existing_events', methods: ['GET'])]
    public function addParticipationExistingEvents(Request $request): JsonResponse
    {
        $userId = $request->query->getInt('user', 0);
        if ($userId <= 0) {
            return new JsonResponse(['success' => true, 'events' => []]);
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Joueur non trouvé.'], 404);
        }

        $regs = $this->registrationRepository->findBy(['user' => $user]);
        $events = array_values(array_unique(array_filter(array_map(
            static fn (Registration $r) => $r->getEvent(),
            $regs
        ))));

        return new JsonResponse(['success' => true, 'events' => $events]);
    }

    #[Route('/admin/import-tickets', name: 'app_admin_import_tickets', methods: ['GET', 'POST'])]
    public function importTickets(Request $request): Response
    {
        $importResult = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('import_tickets', $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Jeton de sécurité invalide. Veuillez réessayer.');
            } else {
            $file = $request->files->get('csv');
            $event = $request->request->get('event');

            if (!$file || !$file->isValid()) {
                $this->addFlash('error', 'Veuillez sélectionner un fichier CSV valide.');
            } elseif (!$event || !\in_array($event, EventType::EVENTS, true)) {
                $this->addFlash('error', 'Veuillez sélectionner un événement.');
            } else {
                $content = file_get_contents($file->getPathname());
                if ($content === false) {
                    $this->addFlash('error', 'Impossible de lire le fichier.');
                } else {
                    $importResult = $this->csvImportService->import($content, $event);
                    $this->addFlash('success', sprintf(
                        'Import terminé : %d créée(s), %d ignorée(s) (billet déjà en base), %d doublon(s) (alerte envoyée).',
                        $importResult['created'],
                        $importResult['ignored'],
                        $importResult['duplicates']
                    ));
                }
            }
            }
        }

        return $this->render('admin/import_tickets.html.twig', [
            'breadcrumb' => [
                '/admin' => 'Administration',
                '/admin/import-tickets' => 'Importer des billets (CSV)',
            ],
            'eventChoices' => EventType::getChoices(),
            'importResult' => $importResult,
        ]);
    }


    #[Route('/api/send-invite', name: 'api_send_invite', methods: ['POST'])]
    public function sendInvite(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data['test'] ?? 0 == 1) {
            $user = $this->getUser();
            /** @var User $user */
            try {
                $this->emailService->sendInviteEmail($user);
            } catch (\Exception $e) {
                return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
            }
        } else {
            $errors = [];
            $players = $this->entityManager->getRepository(User::class)->findAll();
            foreach ($players as $player) {
                if ($player->getPassword() == '') {
                    try {
                        $this->emailService->sendInviteEmail($player);

                        $player->setPassword('1');
                        $this->entityManager->flush();
                    } catch (\Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
            if (!empty($errors)) {
                return new JsonResponse(['success' => false, 'error' => $errors], 500);
            }
        }
        return new JsonResponse(['success' => true]);
    }
}
