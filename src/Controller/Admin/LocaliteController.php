<?php

namespace App\Controller\Admin;

use App\Entity\Localite;
use App\Form\LocaliteType;
use App\Repository\LocaliteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/localite')]
#[IsGranted('ROLE_ADMIN')]
final class LocaliteController extends AbstractController
{
    #[Route(name: 'app_localite_index', methods: ['GET'])]
    public function index(LocaliteRepository $localiteRepository): Response
    {
        return $this->render('localite/index.html.twig', [
            'localites' => $localiteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_localite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $localite = new Localite();
        $form = $this->createForm(LocaliteType::class, $localite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($localite);
            $entityManager->flush();

            return $this->redirectToRoute('app_localite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('localite/new.html.twig', [
            'localite' => $localite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_localite_show', methods: ['GET'])]
    public function show(Localite $localite): Response
    {
        return $this->render('localite/show.html.twig', [
            'localite' => $localite,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_localite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Localite $localite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LocaliteType::class, $localite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_localite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('localite/edit.html.twig', [
            'localite' => $localite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_localite_delete', methods: ['POST'])]
    public function delete(Request $request, Localite $localite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $localite->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($localite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_localite_index', [], Response::HTTP_SEE_OTHER);
    }
}
