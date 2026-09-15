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
use App\Repository\AffectationRepository;
use App\Entity\Affectation;
use App\Form\TransfertType;

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

        if ($form->isSubmitted()) {
            $plainPassword = $form->get('utilisateur')->get('plainPassword')->getData();

            // À la CRÉATION, le mot de passe est OBLIGATOIRE (vérification manuelle)
            if (!$plainPassword) {
                $form->get('utilisateur')->get('plainPassword')->addError(
                    new \Symfony\Component\Form\FormError('Le mot de passe est obligatoire à la création.')
                );
            }

            if ($form->isValid()) {
                $utilisateur = $enseignant->getUtilisateur();
                $utilisateur->setPassword($passwordHasher->hashPassword($utilisateur, $plainPassword));
                $utilisateur->setRoles(['ROLE_ENSEIGNANT']);

                $entityManager->persist($enseignant);
                $entityManager->flush();

                $this->addFlash('success', 'Enseignant créé avec succès !');
                return $this->redirectToRoute('app_enseignant_index');
            }
        }

        return $this->render('admin/enseignant/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_enseignant_edit', methods: ['GET', 'POST'])]
    public function edit(
        Enseignant $enseignant,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(EnseignantType::class, $enseignant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('utilisateur')->get('plainPassword')->getData();

            // Uniquement si l'admin a saisi un NOUVEAU mot de passe
            if ($plainPassword) {
                $utilisateur = $enseignant->getUtilisateur();
                $utilisateur->setPassword($passwordHasher->hashPassword($utilisateur, $plainPassword));
            }

            $entityManager->flush();

            $this->addFlash('success', 'Enseignant modifié avec succès !');
            return $this->redirectToRoute('app_enseignant_index');
        }

        return $this->render('admin/enseignant/edit.html.twig', [
            'enseignant' => $enseignant,
            'form' => $form,
        ]);
    }

    #[Route('/archives', name: 'app_enseignant_archives', methods: ['GET'])]
    public function archives(EnseignantRepository $enseignantRepository): Response
    {
        return $this->render('admin/enseignant/archives.html.twig', [
            'enseignants' => $enseignantRepository->findArchives(),
        ]);
    }

    #[Route('/{id}/archive', name: 'app_enseignant_archive', methods: ['POST'])]
    public function archive(
        Enseignant $enseignant,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérification CSRF : la requête doit venir du formulaire, pas d'un lien piégé
        if ($this->isCsrfTokenValid('archive' . $enseignant->getId(), $request->getPayload()->get('_token'))) {
            $enseignant->getUtilisateur()->setActif(false);
            $entityManager->flush();

            $this->addFlash('success', 'Enseignant archivé.');
        }

        return $this->redirectToRoute('app_enseignant_index');
    }

    #[Route('/{id}/desarchiver', name: 'app_enseignant_desarchiver', methods: ['POST'])]
    public function desarchiver(
        Enseignant $enseignant,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('desarchiver' . $enseignant->getId(), $request->getPayload()->get('_token'))) {
            $enseignant->getUtilisateur()->setActif(true);
            $entityManager->flush();

            $this->addFlash('success', 'Enseignant réactivé.');
        }

        return $this->redirectToRoute('app_enseignant_archives');
    }

    #[Route('/{id}', name: 'app_enseignant_show', methods: ['GET'])]
    public function show(
        Enseignant $enseignant,
        AffectationRepository $affectationRepository
    ): Response {
        // L'affectation EN COURS de cet enseignant (ou null s'il n'en a pas)
        $affectationActive = $affectationRepository->findUneActive($enseignant);
        $historique = $affectationRepository->findHistorique($enseignant);

        return $this->render('admin/enseignant/show.html.twig', [
            'enseignant' => $enseignant,
            'affectationActive' => $affectationActive,
            'historique' => $historique,
        ]);
    }

        #[Route('/{id}/transfert', name: 'app_enseignant_transfert', methods: ['GET', 'POST'])]
    public function transfert(
        Enseignant $enseignant,
        Request $request,
        EntityManagerInterface $entityManager,
        AffectationRepository $affectationRepository
    ): Response {
        // 1. L'affectation actuelle (date_fin NULL) — elle doit exister pour transférer
        $affectationActive = $affectationRepository->findUneActive($enseignant);

        if (!$affectationActive) {
            $this->addFlash('danger', 'Cet enseignant n\'a pas d\'affectation en cours à transférer.');
            return $this->redirectToRoute('app_enseignant_show', ['id' => $enseignant->getId()]);
        }

        // 2. Un retraité ne peut pas être transféré (cohérent avec M8.1 !)
        if ($enseignant->estRetraite()) {
            $this->addFlash('danger', 'Impossible de transférer un enseignant retraité.');
            return $this->redirectToRoute('app_enseignant_show', ['id' => $enseignant->getId()]);
        }

        // 3. Le formulaire de collecte (établissement + date)
        $form = $this->createForm(TransfertType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nouvelEtablissement = $form->get('etablissement')->getData();
            $dateTransfert = $form->get('dateTransfert')->getData();

            // ⭐ TRANSACTION : tout ou rien
            $entityManager->beginTransaction();
            try {
                // 1. FERMER l'ancienne affectation
                $affectationActive->setDateFin($dateTransfert);
                $affectationActive->setStatut('terminee');

                // 2. OUVRIR la nouvelle affectation
                $nouvelle = new Affectation();
                $nouvelle->setEnseignant($enseignant);
                $nouvelle->setEtablissement($nouvelEtablissement);
                $nouvelle->setDateDebut($dateTransfert);
                $nouvelle->setDateFin(null);              // en cours !
                $nouvelle->setStatut('en_cours');
                $nouvelle->setClasse($affectationActive->getClasse());
                $nouvelle->setMatiere($affectationActive->getMatiere());
                $entityManager->persist($nouvelle);

                $entityManager->flush();
                $entityManager->commit();   // ✅ tout est validé

                $this->addFlash('success', 'Enseignant transféré avec succès !');
            } catch (\Exception $e) {
                $entityManager->rollBack(); // ❌ annule tout
                $this->addFlash('danger', 'Le transfert a échoué, aucune modification.');
            }

            return $this->redirectToRoute('app_enseignant_show', ['id' => $enseignant->getId()]);
        }

        return $this->render('admin/enseignant/transfert.html.twig', [
            'enseignant' => $enseignant,
            'affectationActive' => $affectationActive,
            'form' => $form,
        ]);
    }
}
