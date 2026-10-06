<?php

namespace App\Controller\Enseignant;

use App\Entity\Utilisateur;
use App\Form\ChangerMDPType;
use App\Repository\AffectationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/espace')]
#[IsGranted('ROLE_ENSEIGNANT')]
class EspaceController extends AbstractController
{
    #[Route('/', name: 'app_espace')]
    public function index(AffectationRepository $affectationRepository): Response
    {
        // ⭐ L'utilisateur CONNECTÉ (jamais un id venu de l'URL !)
        $utilisateur = $this->getUser();

        // Sécurité + info pour l'IDE : on vérifie que c'est bien NOTRE classe Utilisateur
        if (!$utilisateur instanceof Utilisateur) {
            throw $this->createAccessDeniedException();
        }

        // On traverse la relation 1-1 : compte → SON profil enseignant
        $enseignant = $utilisateur->getEnseignant();

        // Son affectation actuelle (règle métier : date_fin IS NULL)
        $affectationActive = $affectationRepository->findUneActive($enseignant);

        // Son historique complet (pour le compteur du bouton)
        $historique = $affectationRepository->findHistorique($enseignant);

        return $this->render('espace/dashboarde.html.twig', [
            'enseignant'        => $enseignant,
            'affectationActive' => $affectationActive,
            'historique'        => $historique,
        ]);
    }

    #[Route('/historique', name: 'app_espace_historique')]
    public function historique(AffectationRepository $affectationRepository): Response
    {
        $utilisateur = $this->getUser();
        if (!$utilisateur instanceof Utilisateur) {
            throw $this->createAccessDeniedException();
        }

        $enseignant = $utilisateur->getEnseignant();

        return $this->render('espace/historique.html.twig', [
            'enseignant' => $enseignant,
            'historique' => $affectationRepository->findHistorique($enseignant),
        ]);
    }

    #[Route('/mot-de-passe', name: 'app_espace_mot_de_passe', methods: ['GET', 'POST'])]
    public function changerMotDePasse(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // L'utilisateur CONNECTÉ (toujours lui, jamais un id d'URL !)
        $utilisateur = $this->getUser();

        if (!$utilisateur instanceof Utilisateur) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ChangerMDPType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ancien  = $form->get('ancienMotDePasse')->getData();
            $nouveau = $form->get('nouveauMotDePasse')->getData();

            // Vérification n°1 : l'ancien mot de passe est-il correct ?
            if (!$passwordHasher->isPasswordValid($utilisateur, $ancien)) {
                $this->addFlash('danger', 'L\'ancien mot de passe est incorrect.');
                return $this->redirectToRoute('app_espace_mot_de_passe');
            }

            // Vérification n°2 (automatique) : nouveau = confirmation ? (fait par RepeatedType)

            // On HASH le nouveau et on enregistre
            $utilisateur->setPassword($passwordHasher->hashPassword($utilisateur, $nouveau));
            $entityManager->flush();

            $this->addFlash('success', 'Mot de passe modifié avec succès !');
            return $this->redirectToRoute('app_espace');
        }

        return $this->render('espace/mot_de_passe.html.twig', [
            'form' => $form,
        ]);
    }
}