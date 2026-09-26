<?php

namespace App\Controller\Admin;

use App\Repository\AffectationRepository;
use App\Repository\EnseignantRepository;
use App\Repository\EtablissementRepository;
use App\Repository\LocaliteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function index(
        EnseignantRepository $enseignantRepository,
        EtablissementRepository $etablissementRepository,
        AffectationRepository $affectationRepository,
        LocaliteRepository $localiteRepository,
    ): Response {
        return $this->render('admin/dashboarda.html.twig', [
            'nbEnseignants' => $enseignantRepository->count([]),
            'nbEtablissements' => $etablissementRepository->count([]),
            'nbAffectationsEnCours' => $affectationRepository->countEnCours(),
            'nbLocalites' => $localiteRepository->count([]),
        ]);
    }
}
