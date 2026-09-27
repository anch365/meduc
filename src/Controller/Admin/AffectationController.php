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
use App\Form\TransfertType;


#[Route('/admin/affectation')]
#[IsGranted('ROLE_ADMIN')]
class AffectationController extends AbstractController
{
    #[Route('/', name: 'app_affectation_index', methods: ['GET'])]
    public function index(Request $request, AffectationRepository $affectationRepository): Response
    {
        $search = trim($request->query->get('q', ''));
        $sort   = $request->query->get('sort', 'debut');
        $order  = $request->query->get('order', 'DESC');
        $page   = max(1, $request->query->getInt('page', 1));
        $limit  = 10;

        $result = $affectationRepository->findPaginated($search, $sort, $order, $page, $limit);

        return $this->render('admin/affectation/index.html.twig', [
            'affectations' => $result['items'],
            'total'        => $result['total'],
            'totalPages'   => max(1, (int) ceil($result['total'] / $limit)),
            'page'         => $page,
            'search'       => $search,
            'sort'         => $sort,
            'order'        => $order,
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

    #[Route('/{id}/transfert', name: 'app_affectation_transfert', methods: ['GET', 'POST'])]
    public function transfert(
        Affectation $affectation,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        $enseignant = $affectation->getEnseignant();

        // 1. Garde-fou : on ne transfère qu'une affectation EN COURS
        if ($affectation->getDateFin() !== null) {
            $this->addFlash('danger', 'Cette affectation est déjà terminée : transfert impossible.');
            return $this->redirectToRoute('app_affectation_index');
        }

        // 2. Un retraité ne peut pas être transféré (inchangé)
        if ($enseignant->estRetraite()) {
            $this->addFlash('danger', 'Impossible de transférer un enseignant retraité.');
            return $this->redirectToRoute('app_enseignant_show', ['id' => $enseignant->getId()]);
        }

        // 3. Le formulaire (inchangé)
        $form = $this->createForm(TransfertType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nouvelEtablissement = $form->get('etablissement')->getData();
            $dateTransfert = $form->get('dateTransfert')->getData();

            // ⭐ TRANSACTION : tout ou rien (inchangé)
            $entityManager->beginTransaction();
            try {
                // 1. FERMER l'affectation passée en paramètre
                $affectation->setDateFin($dateTransfert);
                $affectation->setStatut('terminee');

                // 2. OUVRIR la nouvelle
                $nouvelle = new Affectation();
                $nouvelle->setEnseignant($enseignant);
                $nouvelle->setEtablissement($nouvelEtablissement);
                $nouvelle->setDateDebut($dateTransfert);
                $nouvelle->setDateFin(null);
                $nouvelle->setStatut('en_cours');
                $nouvelle->setClasse($affectation->getClasse());
                $nouvelle->setMatiere($affectation->getMatiere());
                $entityManager->persist($nouvelle);

                $entityManager->flush();
                $entityManager->commit();

                $this->addFlash('success', 'Enseignant transféré avec succès !');
            } catch (\Exception $e) {
                $entityManager->rollBack();
                $this->addFlash('danger', 'Le transfert a échoué, aucune modification.');
            }

            return $this->redirectToRoute('app_affectation_show', ['id' => $enseignant->getId()]);
        }

        return $this->render('admin/affectation/transfert.html.twig', [
            'enseignant' => $enseignant,
            'affectationActive' => $affectation,
            'form' => $form,
        ]);
    }
}
