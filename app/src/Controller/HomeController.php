<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        } elseif ($this->getUser()->isAdmin()) {
            return $this->redirectToRoute('app_admin');
        } elseif ($this->getUser()->isOrga()) {
            return $this->redirectToRoute('app_orga_characters');
        }

        return $this->redirectToRoute('app_character_index');
    }
}
