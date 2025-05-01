<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class PagesController extends AbstractController
{
    public function __construct(
        private readonly Environment $twig
    ) {}

    #[Route('/pages/{slug}', name: 'app_page')]
    public function show(string $slug): Response
    {
        // Vérifie si le template existe
        $template = "pages/{$slug}.html.twig";
        if (!$this->twig->getLoader()->exists($template)) {
            throw $this->createNotFoundException('La page demandée n\'existe pas.');
        }

        return $this->render($template);
    }
}
