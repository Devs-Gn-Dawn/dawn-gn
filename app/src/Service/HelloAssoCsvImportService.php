<?php

namespace App\Service;

use App\Entity\EventType;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

class HelloAssoCsvImportService
{
    private const CSV_SEPARATOR = ';';
    private const COL_TICKET = 'Numéro de billet';
    private const COL_STATUS = 'Statut de la commande';
    private const COL_EMAIL_CONTACT = 'Adresse Mail de Contact';
    private const COL_EMAIL_PAYER = 'Email payeur';
    private const COL_TARIF = 'Tarif';
    private const COL_FIRSTNAME_PARTICIPANT = 'Prénom participant';
    private const COL_LASTNAME_PARTICIPANT = 'Nom participant';
    private const COL_FIRSTNAME_PAYER = 'Prénom payeur';
    private const COL_LASTNAME_PAYER = 'Nom payeur';
    private const STATUS_VALIDE = 'Validé';

    public function __construct(
        private HelloAssoWebhookService $webhookService,
        private RegistrationRepository $registrationRepository,
        private EntityManagerInterface $em
    ) {}

    /**
     * @return array{linesRead: int, processed: int, created: int, ignored: int, duplicates: int, errors: list<array{line: int, message: string}>}
     */
    public function import(string $csvContent, string $eventType): array
    {
        $result = [
            'linesRead' => 0,
            'processed' => 0,
            'created' => 0,
            'ignored' => 0,
            'duplicates' => 0,
            'errors' => [],
        ];

        if (!self::isValidEventType($eventType)) {
            $result['errors'][] = ['line' => 0, 'message' => 'Événement invalide : ' . $eventType];
            return $result;
        }

        $content = $this->normalizeEncoding($csvContent);
        if (trim($content) === '') {
            $result['errors'][] = ['line' => 0, 'message' => 'Fichier CSV vide ou invalide'];
            return $result;
        }

        $lines = preg_split('/\r\n|\r|\n/', $content);
        if ($lines === false || empty($lines)) {
            $result['errors'][] = ['line' => 0, 'message' => 'Fichier CSV vide ou invalide'];
            return $result;
        }

        $header = array_map('trim', str_getcsv(array_shift($lines), self::CSV_SEPARATOR));
        $result['linesRead'] = count($lines);

        $registrationsCreated = [];

        foreach ($lines as $index => $line) {
            $lineNum = $index + 2; // 1-based, +1 for header
            $row = str_getcsv($line, self::CSV_SEPARATOR);
            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            }
            $assoc = array_combine($header, array_slice($row, 0, count($header)));
            if ($assoc === false) {
                $result['errors'][] = ['line' => $lineNum, 'message' => 'Ligne invalide'];
                continue;
            }

            $assoc = array_map('trim', $assoc);

            $status = $assoc[self::COL_STATUS] ?? '';
            if ($status !== self::STATUS_VALIDE) {
                continue;
            }

            $ticketId = $assoc[self::COL_TICKET] ?? '';
            if ($ticketId === '') {
                $result['errors'][] = ['line' => $lineNum, 'message' => 'Numéro de billet manquant'];
                continue;
            }

            $email = trim((string)($assoc[self::COL_EMAIL_CONTACT] ?? ''));
            if ($email === '') {
                $email = trim((string)($assoc[self::COL_EMAIL_PAYER] ?? ''));
            }
            if ($email === '') {
                $result['errors'][] = ['line' => $lineNum, 'message' => 'Email manquant'];
                continue;
            }

            $firstName = $assoc[self::COL_FIRSTNAME_PARTICIPANT] ?? $assoc[self::COL_FIRSTNAME_PAYER] ?? '';
            $lastName = $assoc[self::COL_LASTNAME_PARTICIPANT] ?? $assoc[self::COL_LASTNAME_PAYER] ?? '';
            $itemName = $assoc[self::COL_TARIF] ?? null;
            $itemName = $itemName !== '' ? $itemName : null;

            // exclude certains item's names
            $excludedItemNames = ['Place PNJ'];
            if (in_array($itemName ?? '', $excludedItemNames)) {
                continue;
            }

            $result['processed']++;

            $existingByTicket = $this->registrationRepository->findOneBy(['helloasso_ticket' => $ticketId]);
            if ($existingByTicket !== null) {
                $result['ignored']++;
                continue;
            }

            $registration = $this->webhookService->processOneRegistration(
                $ticketId,
                $email,
                $firstName,
                $lastName,
                $itemName,
                $eventType,
                $registrationsCreated
            );

            if ($registration !== null) {
                $result['created']++;
            } else {
                $result['duplicates']++;
            }
        }

        $this->em->flush();

        return $result;
    }

    private function normalizeEncoding(string $content): string
    {
        $bom = "\xEF\xBB\xBF";
        if (str_starts_with($content, $bom)) {
            $content = substr($content, strlen($bom));
        }
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }
        return $content;
    }

    private static function isValidEventType(string $eventType): bool
    {
        try {
            EventType::from($eventType);
            return true;
        } catch (\ValueError) {
            return false;
        }
    }
}
