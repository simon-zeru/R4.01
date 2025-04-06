<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Produit;
use App\Form\ProduitType;

#[Route(path: '/{_locale}/produit', requirements: ['_locale'=>'%app.supported_locales%'])]
final class ProduitController extends AbstractController
{
    #[Route('/{idProduit}', name: 'app_produit', requirements:['idProduit' => '\d+'], methods: ['GET'])]
    public function detail(ProduitRepository $produitRepository, CommentaireRepository $commentaireRepository, int $idProduit): Response
    {
        $produit = $produitRepository->find($idProduit);

        if ($produit==null) {
            $this->createNotFoundException("Le produit numéro ".$idProduit." n'existe pas.");
        }

        $commentaires = $commentaireRepository->findBy(['produit' => $produit], ['id' => 'DESC']); 

        $noteMoyenne = null;
        $totalNotes = 0;
        if (count($commentaires) > 0) {
            foreach ($commentaires as $commentaire) {
                $totalNotes += $commentaire->getNote(); // Assure-toi que getNote() existe dans Commentaire
            }
            $noteMoyenne = $totalNotes / count($commentaires);
        }

        return $this->render('produit/detail.html.twig', [
            'produit' => $produit, 
            'commentaires' => $commentaires,
            'noteMoyenne' => $noteMoyenne, 
        ]);
    }
    #[Route('/{idProduit}/commenter', name: 'app_produit_commenter', requirements:['idProduit' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function ajouterCommentaire(Request $request, EntityManagerInterface $entityManager, ProduitRepository $produitRepository, int $idProduit): Response
    {
        $produit = $produitRepository->find($idProduit);

        if ($produit==null) {
            $this->createNotFoundException("Le produit numéro ".$idProduit." n'existe pas.");
        }

        $commentaire = new Commentaire();

        $form = $this->createForm(CommentaireType::class, $commentaire); 
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Poster le commentaire
            $commentaire->setProduit($produit);
            $commentaire->setUsager($this->getUser());

            // Sauvegarder le commentaire en base de données
            $entityManager->persist($commentaire);
            $entityManager->flush();
            return $this->redirectToRoute('app_produit', ['idProduit' => $idProduit], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/commenter.html.twig', [
            'produit' => $produit, // Pour afficher le nom du produit sur la page du formulaire
            'commentaireForm' => $form->createView(), // Passe la VUE du formulaire au template
        ]);
    }

    #[Route(name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

}