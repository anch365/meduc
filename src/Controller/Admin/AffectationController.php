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
    #[Route('/', name: 'app_affectation_index', methods: ['GET'])]
    public function index(AffectationRepository $affectationRepository): Response
    {
        return $this->render('admin/affectation/index.html.twig', [
            'affectations' => $affectationRepository->findBy([], ['dateDebut' => 'DESC']),
        ]);
    }
    
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

            $enseignant = $affectation->getEnseignant();

            // ⭐ RÈGLE n°1 : l'enseignant est-il RETRAITÉ ?
            if ($enseignant->estRetraite()) {
                $form->addError(new FormError(
                    'Impossible : cet enseignant a atteint l\'âge de la retraite.'
                ));
            }

            // ⭐ RÈGLE n°2 : a-t-il déjà une affectation OUVERTE ?
            $affectationExistante = $affectationRepository->findUneActive($enseignant);

            if ($affectationExistante) {

                $form->addError(new FormError('Cet enseignant a déjà une affectation en cours !'));
            }

            // On n'enregistre QUE si aucune règle n'a ajouté d'erreur
            if ($form->isValid()) {
                $affectation->setDateFin(null); // en cours
                $affectation->setStatut('en_cours');
                $entityManager->persist($affectation);
                $entityManager->flush();

                // On envoie l'admin sur la FICHE de l'enseignant concerné
                return $this->redirectToRoute('app_enseignant_show', [
                    'id' => $enseignant->getId(),
                ]);
            }
        }

        return $this->render('admin/affectation/new.html.twig', [
            'form' => $form,
        ]);
    }
}
