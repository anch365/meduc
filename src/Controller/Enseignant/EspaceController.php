<?php

namespace App\Controller\Enseignant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/espace')]
#[IsGranted('ROLE_ENSEIGNANT')]
class EspaceController extends AbstractController
{
    #[Route('/', name: 'app_espace')]
    public function index(): Response
    {
        return $this->render('espace/dashboarde.html.twig');
    }
}