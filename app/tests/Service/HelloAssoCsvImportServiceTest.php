<?php

namespace App\Tests\Service;

use App\Entity\Registration;
use App\Entity\User;
use App\Repository\RegistrationRepository;
use App\Service\HelloAssoCsvImportService;
use App\Service\HelloAssoWebhookService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class HelloAssoCsvImportServiceTest extends TestCase
{
    private HelloAssoWebhookService $webhookService;
    private RegistrationRepository $registrationRepository;
    private EntityManagerInterface $em;
    private HelloAssoCsvImportService $service;

    protected function setUp(): void
    {
        $this->webhookService = $this->createMock(HelloAssoWebhookService::class);
        $this->registrationRepository = $this->createMock(RegistrationRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);

        $this->service = new HelloAssoCsvImportService(
            $this->webhookService,
            $this->registrationRepository,
            $this->em
        );
    }

    public function testImportReturnsErrorWhenEventTypeInvalid(): void
    {
        $csv = "Numéro de billet;Statut de la commande;Adresse Mail de Contact\n123;Validé;a@b.com";
        $result = $this->service->import($csv, 'invalid-event');

        self::assertSame(1, count($result['errors']));
        self::assertStringContainsString('Événement invalide', $result['errors'][0]['message']);
        self::assertSame(0, $result['created']);
        self::assertSame(0, $result['processed']);
    }

    public function testImportReturnsErrorWhenCsvEmpty(): void
    {
        $result = $this->service->import('', 'dawn39');

        self::assertSame(1, count($result['errors']));
        self::assertStringContainsString('vide', $result['errors'][0]['message']);
    }

    public function testImportParsesValidCsvAndCallsWebhookService(): void
    {
        $csv = <<<CSV
Référence commande;Date de la commande;Statut de la commande;Nom participant;Prénom participant;Nom payeur;Prénom payeur;Email payeur;Raison sociale;Moyen de paiement;Billet;Numéro de billet;Tarif;Montant tarif;Code Promo;Montant code promo;Adresse Mail de Contact;Droit à L'image;Nom de votre personnage
172754126;04/03/2026 14:25;Validé;VIDA;Kévin;VIDA;Kévin;kevin.vida@laposte.net;;Carte bancaire;url;172754126;Place PJ Tech'ers;85,00;;;kevin.vida@laposte.net;Oui;James
CSV;

        $this->registrationRepository->method('findOneBy')->with(['helloasso_ticket' => '172754126'])->willReturn(null);

        $registration = new Registration();
        $registration->setHelloassoTicket('172754126');
        $registration->setEvent('dawn39');
        $this->webhookService->expects(self::once())
            ->method('processOneRegistration')
            ->with(
                '172754126',
                'kevin.vida@laposte.net',
                'Kévin',
                'VIDA',
                "Place PJ Tech'ers",
                'dawn39',
                self::isType('array')
            )
            ->willReturn($registration);

        $this->em->expects(self::once())->method('flush');

        $result = $this->service->import($csv, 'dawn39');

        self::assertSame(1, $result['linesRead']);
        self::assertSame(1, $result['processed']);
        self::assertSame(1, $result['created']);
        self::assertSame(0, $result['ignored']);
        self::assertSame(0, $result['duplicates']);
        self::assertSame(0, count($result['errors']));
    }

    public function testImportIgnoresLineWhenTicketAlreadyInDatabase(): void
    {
        $csv = <<<CSV
Référence commande;Statut de la commande;Numéro de billet;Adresse Mail de Contact;Prénom participant;Nom participant
1;Validé;999888;existing@example.com;Jean;Dupont
CSV;

        $existingRegistration = new Registration();
        $existingRegistration->setHelloassoTicket('999888');
        $this->registrationRepository->method('findOneBy')->with(['helloasso_ticket' => '999888'])->willReturn($existingRegistration);

        $this->webhookService->expects(self::never())->method('processOneRegistration');
        $this->em->expects(self::once())->method('flush');

        $result = $this->service->import($csv, 'dawn39');

        self::assertSame(1, $result['processed']);
        self::assertSame(0, $result['created']);
        self::assertSame(1, $result['ignored']);
    }

    public function testImportSkipsLinesWithStatusNotValide(): void
    {
        $csv = <<<CSV
Référence commande;Statut de la commande;Numéro de billet;Adresse Mail de Contact;Prénom participant;Nom participant
1;En attente;111;a@b.com;A;B
CSV;

        $this->registrationRepository->expects(self::never())->method('findOneBy');
        $this->webhookService->expects(self::never())->method('processOneRegistration');
        $this->em->expects(self::once())->method('flush');

        $result = $this->service->import($csv, 'dawn39');

        self::assertSame(1, $result['linesRead']);
        self::assertSame(0, $result['processed']);
        self::assertSame(0, $result['created']);
    }

    public function testImportUsesEmailPayerWhenAdresseMailContactEmpty(): void
    {
        $csv = "Numéro de billet;Statut de la commande;Adresse Mail de Contact;Email payeur;Prénom participant;Nom participant;Tarif\n"
            . "555;Validé;;payer@example.com;P;ayer;Place PNJ";

        $this->registrationRepository->method('findOneBy')->willReturn(null);
        $this->webhookService->expects(self::once())
            ->method('processOneRegistration')
            ->with(
                '555',
                'payer@example.com',
                self::anything(),
                self::anything(),
                'Place PNJ',
                'dawn39',
                self::isType('array')
            )
            ->willReturn(new Registration());

        $this->em->expects(self::once())->method('flush');

        $result = $this->service->import($csv, 'dawn39');
        self::assertSame(1, $result['processed']);
        self::assertSame(1, $result['created']);
    }

    public function testImportAddsErrorWhenTicketIdMissing(): void
    {
        $csv = <<<CSV
Référence commande;Statut de la commande;Numéro de billet;Adresse Mail de Contact;Prénom participant;Nom participant
1;Validé;;a@b.com;A;B
CSV;

        $this->webhookService->expects(self::never())->method('processOneRegistration');
        $this->em->expects(self::once())->method('flush');

        $result = $this->service->import($csv, 'dawn39');

        self::assertSame(1, count($result['errors']));
        self::assertStringContainsString('Numéro de billet', $result['errors'][0]['message']);
        self::assertSame(0, $result['created']);
    }

    public function testImportAddsErrorWhenEmailMissing(): void
    {
        $csv = <<<CSV
Référence commande;Statut de la commande;Numéro de billet;Adresse Mail de Contact;Email payeur;Prénom participant;Nom participant
1;Validé;123;;;A;B
CSV;

        $this->registrationRepository->method('findOneBy')->willReturn(null);
        $this->webhookService->expects(self::never())->method('processOneRegistration');
        $this->em->expects(self::once())->method('flush');

        $result = $this->service->import($csv, 'dawn39');

        self::assertSame(1, count($result['errors']));
        self::assertStringContainsString('Email', $result['errors'][0]['message']);
    }
}
