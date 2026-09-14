<?php

namespace App\Controller\Admin;

use App\Entity\Affectation;
use App\Form\AffectationType;
use App\Repository\AffectationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\FormError;

#[Route('/admin/affectation')]
#[IsGranted('ROLE_ADMIN')]
class AffectationController extends AbstractController
{
    #[Route('/new', name: 'app_affectation_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        AffectationRepository $affectationRepository
    ): Response {
        $affectation = new Affectation();
        $form = $this->createForm(AffectationType::class, $affectation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // LA RÈGLE MÉTIER : l'enseignant a-t-il déjà une affectation OUVERTE ?
            $enseignant = $affectation->getEnseignant();
            $affectationExistante = $affectationRepository->findUneActive($enseignant);

            if ($affectationExistante) {
                //Erreur attachée au FORMULAIRE : elle s'affiche IMMÉDIATEMENT
                $form->addError(new FormError('Cet enseignant a déjà une affectation en cours !'));
            } else {
                $affectation->setDateFin(null); // en cours
                $affectation->setStatut('en_cours');
                $entityManager->persist($affectation);
                $entityManager->flush();

                // On envoie l'admin sur la FICHE de l'enseignant concerné
                return $this->redirectToRoute('app_enseignant_show', [
                    'id' => $affectation->getEnseignant()->getId(),
                ]);
            }
        }

        return $this->render('admin/affectation/new.html.twig', [
            'form' => $form,
        ]);
    }
}
