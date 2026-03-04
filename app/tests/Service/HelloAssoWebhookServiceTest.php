<?php

namespace App\Tests\Service;

use App\Entity\Registration;
use App\Entity\User;
use App\Repository\RegistrationRepository;
use App\Repository\UserRepository;
use App\Service\EmailService;
use App\Service\HelloAssoWebhookService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class HelloAssoWebhookServiceTest extends TestCase
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;
    private EmailService $emailService;
    private UserRepository $userRepository;
    private RegistrationRepository $registrationRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private HelloAssoWebhookService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->emailService = $this->createMock(EmailService::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->registrationRepository = $this->createMock(RegistrationRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed');

        $this->service = new HelloAssoWebhookService(
            $this->em,
            $this->logger,
            ['51.138.206.200', '4.233.135.234'],
            $this->emailService,
            $this->userRepository,
            $this->registrationRepository,
            ['dawn-39' => 'dawn39'],
            $this->passwordHasher
        );
    }

    public function testIsClientIpAllowedReturnsTrueForProductionIp(): void
    {
        self::assertTrue($this->service->isClientIpAllowed('51.138.206.200'));
    }

    public function testIsClientIpAllowedReturnsTrueForTestIp(): void
    {
        self::assertTrue($this->service->isClientIpAllowed('4.233.135.234'));
    }

    public function testIsClientIpAllowedReturnsFalseForUnknownIp(): void
    {
        self::assertFalse($this->service->isClientIpAllowed('192.168.1.1'));
    }

    public function testIsClientIpAllowedReturnsFalseForNull(): void
    {
        self::assertFalse($this->service->isClientIpAllowed(null));
    }

    public function testIsClientIpAllowedReturnsFalseForEmptyString(): void
    {
        self::assertFalse($this->service->isClientIpAllowed(''));
    }

    public function testProcessPaymentProcessedWithDataItemsCreatesRegistration(): void
    {
        $payload = [
            'eventType' => 'Payment',
            'data' => [
                'id' => 123456,
                'state' => 'Processed',
                'payer' => [
                    'email' => 'payer@example.com',
                    'firstName' => 'Jean',
                    'lastName' => 'Dupont',
                    'phone' => '0612345678',
                ],
                'order' => [
                    'id' => 123456,
                    'formSlug' => 'dawn-39',
                    'formType' => 'Event',
                ],
                'items' => [
                    [
                        'id' => 164292269,
                        'type' => 'Registration',
                        'name' => 'Place PJ Nomads',
                        'customFields' => [
                            [
                                'id' => 6626657,
                                'name' => 'Adresse Mail de Contact',
                                'answer' => 'nouveau.user@example.com',
                            ],
                        ],
                        'user' => ['firstName' => 'Jean', 'lastName' => 'Dupont'],
                    ],
                ],
            ],
        ];

        $this->userRepository->method('findOneBy')->willReturn(null);
        $this->registrationRepository->method('findOneBy')->willReturn(null);

        $persisted = [];
        $this->em->expects(self::atLeastOnce())->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            $persisted[] = $entity;
        });
        $this->em->expects(self::atLeastOnce())->method('flush');

        $this->emailService->expects(self::once())->method('sendWebhookInviteEmail')->with(self::isInstanceOf(User::class));

        $this->service->process($payload);

        $registrations = array_values(array_filter($persisted, fn ($e) => $e instanceof Registration));
        self::assertCount(1, $registrations, 'Exactly one Registration should be created');
        self::assertSame('164292269', $registrations[0]->getHelloassoTicket());
        self::assertSame('dawn39', $registrations[0]->getEvent());
    }

    public function testProcessPaymentProcessedIdempotenceSkipsExistingTicket(): void
    {
        $payload = [
            'eventType' => 'Payment',
            'data' => [
                'state' => 'Processed',
                'payer' => ['email' => 'a@b.com', 'firstName' => 'A', 'lastName' => 'B'],
                'order' => ['id' => 1, 'formSlug' => 'dawn-39', 'formType' => 'Event'],
                'items' => [
                    [
                        'id' => 999,
                        'type' => 'Registration',
                        'name' => 'Place',
                        'customFields' => [['id' => 6626657, 'name' => 'Adresse Mail de Contact', 'answer' => 'a@b.com']],
                        'user' => ['firstName' => 'A', 'lastName' => 'B'],
                    ],
                ],
            ],
        ];

        $existingRegistration = new Registration();
        $this->userRepository->method('findOneBy')->willReturn(null);
        $this->registrationRepository->method('findOneBy')->with(['helloasso_ticket' => '999'])->willReturn($existingRegistration);

        $this->em->expects(self::never())->method('persist');
        $this->em->expects(self::atLeastOnce())->method('flush'); // confirmOrder calls flush() at end

        $this->service->process($payload);
    }

    public function testProcessPaymentProcessedSkipsItemWhenEmailMissing(): void
    {
        $payload = [
            'eventType' => 'Payment',
            'data' => [
                'state' => 'Processed',
                'payer' => [], // no email
                'order' => ['id' => 1, 'formSlug' => 'dawn-39', 'formType' => 'Event'],
                'items' => [
                    [
                        'id' => 888,
                        'type' => 'Registration',
                        'name' => 'Place',
                        'customFields' => [], // no email field
                        'user' => ['firstName' => 'X', 'lastName' => 'Y'],
                    ],
                ],
            ],
        ];

        $this->registrationRepository->method('findOneBy')->willReturn(null);

        $this->em->expects(self::never())->method('persist');
        $this->em->expects(self::atLeastOnce())->method('flush');

        $this->service->process($payload);
    }

    public function testProcessPaymentProcessedDuplicateEventSendsAlert(): void
    {
        $user = new User();
        $user->setId(1);
        $user->setEmail('dup@example.com');
        $user->setFirstname('Dup');
        $user->setName('User');

        $existingRegistration = new Registration();
        $existingRegistration->setId(10);
        $existingRegistration->setUser($user);
        $existingRegistration->setEvent('dawn39');
        $existingRegistration->setHelloassoTicket('111');

        $payload = [
            'eventType' => 'Payment',
            'data' => [
                'state' => 'Processed',
                'payer' => ['email' => 'payer@example.com', 'firstName' => 'P', 'lastName' => 'D'],
                'order' => ['id' => 2, 'formSlug' => 'dawn-39', 'formType' => 'Event'],
                'items' => [
                    [
                        'id' => 222,
                        'type' => 'Registration',
                        'name' => 'Place',
                        'customFields' => [['id' => 6626657, 'name' => 'Adresse Mail de Contact', 'answer' => 'dup@example.com']],
                        'user' => ['firstName' => 'Dup', 'lastName' => 'User'],
                    ],
                ],
            ],
        ];

        $this->userRepository->method('findOneBy')->with(['email' => 'dup@example.com'])->willReturn($user);
        $this->registrationRepository->method('findOneBy')->willReturnCallback(function (array $criteria) use ($existingRegistration) {
            if (isset($criteria['helloasso_ticket']) && $criteria['helloasso_ticket'] === '222') {
                return null;
            }
            if (isset($criteria['user'], $criteria['event']) && $criteria['event'] === 'dawn39') {
                return $existingRegistration;
            }
            return null;
        });

        $this->emailService->expects(self::once())
            ->method('sendAdminDuplicateRegistrationAlertEmail')
            ->with(self::identicalTo($user), self::identicalTo($existingRegistration), self::isType('array'));

        $this->em->expects(self::never())->method('persist');
        $this->em->expects(self::atLeastOnce())->method('flush');

        $this->service->process($payload);
    }

    public function testProcessPaymentRefundedRemovesRegistration(): void
    {
        $payload = [
            'eventType' => 'Payment',
            'data' => [
                'state' => 'Refunded',
                'payer' => ['email' => 'a@b.com'],
                'order' => ['id' => 3, 'formSlug' => 'dawn-39', 'formType' => 'Event'],
                'items' => [
                    [
                        'id' => 777,
                        'type' => 'Registration',
                        'name' => 'Place',
                        'customFields' => [],
                        'user' => ['firstName' => 'A', 'lastName' => 'B'],
                    ],
                ],
            ],
        ];

        $registrationToRemove = new Registration();
        $this->registrationRepository->method('findOneBy')->with(['helloasso_ticket' => '777'])->willReturn($registrationToRemove);

        $this->em->expects(self::once())->method('remove')->with(self::identicalTo($registrationToRemove));
        $this->em->expects(self::once())->method('flush');

        $this->service->process($payload);
    }

    public function testProcessUsesOrderItemsAsFallback(): void
    {
        $payload = [
            'eventType' => 'Payment',
            'data' => [
                'state' => 'Processed',
                'payer' => ['email' => 'fallback@example.com', 'firstName' => 'F', 'lastName' => 'B'],
                'order' => [
                    'id' => 4,
                    'formSlug' => 'dawn-39',
                    'formType' => 'Event',
                    'items' => [
                        [
                            'id' => 555,
                            'type' => 'Registration',
                            'name' => 'Place',
                            'customFields' => [['id' => 6626657, 'name' => 'Adresse Mail de Contact', 'answer' => 'fallback@example.com']],
                            'user' => ['firstName' => 'F', 'lastName' => 'B'],
                        ],
                    ],
                ],
                // 'items' key omitted so fallback to order.items is used
            ],
        ];

        $this->userRepository->method('findOneBy')->willReturn(null);
        $this->registrationRepository->method('findOneBy')->willReturn(null);

        $persisted = [];
        $this->em->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            $persisted[] = $entity;
        });
        $this->em->expects(self::atLeastOnce())->method('flush');

        $this->emailService->method('sendWebhookInviteEmail');

        $this->service->process($payload);

        $registrations = array_values(array_filter($persisted, fn ($e) => $e instanceof Registration));
        self::assertCount(1, $registrations, 'Fallback to order.items should still create one registration');
        self::assertSame('555', $registrations[0]->getHelloassoTicket());
    }

    public function testProcessOneRegistrationCreatesRegistration(): void
    {
        $this->userRepository->method('findOneBy')->willReturn(null);
        $this->registrationRepository->method('findOneBy')->willReturn(null);

        $persisted = [];
        $this->em->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            $persisted[] = $entity;
        });
        $this->em->expects(self::atLeastOnce())->method('flush');

        $this->emailService->expects(self::once())->method('sendWebhookInviteEmail')->with(self::isInstanceOf(User::class));

        $registrationsCreated = [];
        $registration = $this->service->processOneRegistration(
            '12345',
            'csv.user@example.com',
            'Jean',
            'Dupont',
            'Place PJ',
            'dawn39',
            $registrationsCreated
        );

        self::assertInstanceOf(Registration::class, $registration);
        self::assertSame('12345', $registration->getHelloassoTicket());
        self::assertSame('dawn39', $registration->getEvent());
        self::assertSame('Place PJ', $registration->getItemName());
        self::assertCount(1, $registrationsCreated);
    }

    public function testProcessOneRegistrationReturnsNullWhenTicketAlreadyExists(): void
    {
        $existing = new Registration();
        $existing->setHelloassoTicket('99999');
        $this->userRepository->method('findOneBy')->willReturn(null);
        $this->registrationRepository->method('findOneBy')->with(['helloasso_ticket' => '99999'])->willReturn($existing);

        $this->em->expects(self::never())->method('persist');

        $registrationsCreated = [];
        $registration = $this->service->processOneRegistration(
            '99999',
            'existing@example.com',
            'A',
            'B',
            null,
            'dawn39',
            $registrationsCreated
        );

        self::assertNull($registration);
        self::assertCount(0, $registrationsCreated);
    }
}
