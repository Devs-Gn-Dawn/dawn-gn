<?php

namespace App\Controller;

use FPDF;
use App\Entity\Character;
use App\Entity\FactionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Color\Color;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PdfController extends AbstractController
{
    private LoggerInterface $logger;
    public function __construct(
        private EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        private UrlGeneratorInterface $urlGenerator
    ) {
        $this->logger = $logger;
    }

    private function mbCell($pdf, $x, $y, $text, $size = 12, $width = 0, $align = '')
    {
        if (!is_null($x) && !is_null($y)) {
            $pdf->SetXY($x, $y);
        }
        //avec bordure : $pdf->Cell($width, $size * 0.4, mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8'), 1, 0, $align);
        $pdf->Cell($width, $size * 0.4, mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8'), 0, 0, $align);
    }

    private function mbMultiCell($pdf, $x, $y, $text, $size = 12, $width = 0, $align = '')
    {
        if (!is_null($x) && !is_null($y)) {
            $pdf->SetXY($x, $y);
        }
        //avec bordure : $pdf->MultiCell($width, $size * 0.4, mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8'), 1, $align);
        $pdf->MultiCell($width, $size * 0.4, mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8'), 0, $align);
    }

    private function mbWrite($pdf, $text, $size = 12)
    {
        $pdf->Write($size * 0.4, mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8'));
    }

    #[Route('/pdf/{id}', name: 'app_pdf')]
    public function generatePdf(int $id): Response
    {
        $user = $this->getUser();
        /** @var User $user */
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $character = $this->entityManager->getRepository(Character::class)->find($id);

        if (!$character) {
            throw $this->createNotFoundException('Personnage non trouvé');
        }

        if ($character->getUser() !== $user) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        // Création du QR code
        $qrCode = new QrCode($this->generateUrl('app_character_check', ['id' => $id], UrlGeneratorInterface::ABSOLUTE_URL));
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Création d'une image temporaire à partir des données PNG
        $tempFile = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
        file_put_contents($tempFile, $result->getString());

        $background = FactionType::getCharcaterSheetBackground($character->getFactionType());
        $imagePath = $this->getParameter('kernel.project_dir') . '/public/build/images/templates_pj/' . $background . '.jpg';

        // Création du PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->Image($imagePath, 0, 0, 210, 297, 'JPG');

        // Ajout du QR code
        $pdf->Image($tempFile, 19, 13, 40, 40, 'PNG');

        // Suppression du fichier temporaire
        unlink($tempFile);

        // Ajout du numéro de billet
        $pdf->SetFont('Arial', '', 12);
        $this->mbCell($pdf, 33, 53.7, $user->lastRegistrationWithState(constant('App\Entity\EventType::STATUS_OPEN'))->getHelloassoTicket(), 12, 28);

        // user name
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(180, 180, 180);
        $this->mbCell($pdf, 17, 63, $user->getFirstname(), 14, 45);
        $this->mbCell($pdf, 17, 70, $user->getName(), 14, 45);

        // user class
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(51, 51, 51);
        $this->mbCell($pdf, 73, 17.6, $character->getClass(), 11, 80);

        // Configuration de la police
        $pdf->SetFont('Arial', 'B', 16);
        $this->mbCell($pdf, 73, 11, $character->getName(), 14, 80);

        $pdf->SetFont('Arial', '', 12);
        $this->mbCell($pdf, 73, 17.6, $character->getClass(), 11, 80);

        $this->mbCell($pdf, 75.2, 32, $character->getPvMax(), 16, 7, 'C');
                   //($pdf, $x, $y, $text, $size = 12, $width = 0, $align = '')
        $this->mbCell($pdf, 114.5, 32, $character->getArmor(), 16, 7, 'C');

        // skills learned
        $pdf->SetY(53);
        $pdf->SetLeftMargin(75);
        foreach ($character->getSkillsLearned() as $skillLearned) {
            $pdf->SetX(75);
            $pdf->SetFont('Arial', 'B', 9);
            $this->mbWrite($pdf, $skillLearned->getSkill()->getLabel() . ' :', 8);
            $pdf->SetFont('Arial', '', 9);
            $this->mbWrite($pdf, ' '. $skillLearned->getSkill()->getShort(), 8);
            $pdf->Ln(4);
        }

        // possessions
        $pdf->SetY(206);
        foreach ($character->getPossessions() as $possession) {
            $pdf->SetX(75);
            $pdf->SetFont('Arial', 'B', 9);
            $this->mbWrite($pdf, $possession->getGear()->getLabel() . ' :', 8);
            $pdf->SetFont('Arial', '', 9);
            $this->mbWrite($pdf, ' '. $possession->getGear()->getShort(), 8);
            $pdf->Ln(4);
        }

        // radiations levels
        $y = 184.6;
        $radOffset = $character->getRadiationsOffset();
        for ($radiationLevel = 1; $radiationLevel <= 10; $radiationLevel++) {
            $displayRad = $radiationLevel == 1 && $radOffset > 0 ? '1-' . ($radOffset + 1) : $radOffset + $radiationLevel;
            $pdf->SetFont('Arial', '', 10);
            $this->mbCell($pdf, 11, $y, $displayRad, 10, 7, 'C');
            $y += 5.04;
        }

        // Génération du PDF
        return new Response(
            // output en mode dowload
            //$pdf->Output('character_sheet.pdf', 'D'),
            // output en mode live
            $pdf->Output('character_sheet.pdf', 'I'),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="character_sheet.pdf"'
            ]
        );
    }
}
