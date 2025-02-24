<?php

namespace App\Controller;
use App\Service\PanierService;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/{_locale}/panier/', name: 'app_panier_index')]
    public function index(PanierService $panierService): Response
    {
        // Récupération du contenu du panier via le service
        $contenuPanier = $panierService->getContenu();
        $total = $panierService->getTotal();

        return $this->render('panier/index.html.twig', [
            'panier' => $contenuPanier,
            'total' => $total,
        ]);
    }

    #[Route('/{_locale}/panier/ajouter/{idProduit}/{quantite}', name: 'app_panier_ajouter')]
    public function ajouter(PanierService $panierService, ProduitRepository $produit, $idProduit, $quantite): Response
    {
        if ($produit->find($idProduit) == null) {
            throw $this->createNotFoundException('Le produit n\'existe pas');
        }

        $panierService->ajouterProduit($idProduit, $quantite);

        return $this->redirectToRoute('app_panier_index');

    }
    #[Route('/{_locale}/panier/supprimer/{idProduit}', name: 'app_panier_supprimer')]
    public function supprimer(PanierService $panierService, ProduitRepository $produit, $idProduit, $quantite): Response
    {

        if ($produit->find($idProduit) == null) {
            throw $this->createNotFoundException('Le produit n\'existe pas');
        }

        $panierService->supprimerProduit($idProduit);

        return $this->redirectToRoute('app_panier_index');

    }
    #[Route('/{_locale}/panier/enlever/{idProduit}/{quantite}', name: 'app_panier_enlever')]
    public function enlever(PanierService $panierService, ProduitRepository $produit, $idProduit, $quantite): Response
    {
        if ($produit->find($idProduit) == null) {
            throw $this->createNotFoundException('Le produit n\'existe pas');
        }
        $panierService->enleverProduit($idProduit, $quantite);

        return $this->redirectToRoute('app_panier_index');

    }

    #[Route('/{_locale}/panier/vider', name: 'app_panier_vider')]
    public function vider(PanierService $panierService, $idProduit, $quantite): Response
    {

        $panierService->vider();

        return $this->redirectToRoute('app_panier_index');

    }


}
