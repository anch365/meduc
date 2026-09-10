<?php

namespace App\Controller\Admin;

use App\Entity\Enseignant;
use App\Form\EnseignantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\EnseignantRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/enseignant')]
#[IsGranted('ROLE_ADMIN')]
class EnseignantController extends AbstractController
{
        #[Route('/', name: 'app_enseignant_index', methods: ['GET'])]
    public function index(EnseignantRepository $enseignantRepository): Response
    {
        return $this->render('admin/enseignant/index.html.twig', [
            'enseignants' => $enseignantRepository->findActifs(),
        ]);
    }

    #[Route('/new', name: 'app_enseignant_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // 1. On crée un Enseignant VIDE (avec son Utilisateur imbriqué)
        $enseignant = new Enseignant();
        $enseignant->setUtilisateur(new \App\Entity\Utilisateur());

        // 2. On construit le formulaire et on l'écoute
        $form = $this->createForm(EnseignantType::class, $enseignant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 3. Le mot de passe saisi (champ non mappé)
            $plainPassword = $form->get('utilisateur')->get('plainPassword')->getData();

            // 4. On le HASHE et on le met sur le compte
            $utilisateur = $enseignant->getUtilisateur();
            $utilisateur->setPassword($passwordHasher->hashPassword($utilisateur, $plainPassword));

            // 5. On FORCE le rôle enseignant (sécurité : jamais choisi par le client !)
            $utilisateur->setRoles(['ROLE_ENSEIGNANT']);

            // 6. On enregistre TOUT (le compte + le profil liés)
            $entityManager->persist($enseignant);
            $entityManager->flush();

            $this->addFlash('success', 'Enseignant créé avec succès !');
            return $this->redirectToRoute('app_enseignant_new');
        }

        return $this->render('admin/enseignant/new.html.twig', [
            'form' => $form,
        ]);
    }
}