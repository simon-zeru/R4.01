<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BestSellersController extends AbstractController
{

    /**
     * Action pour récupérer et afficher les best-sellers
     * Incluse dans le template de base
     */
    public function bestSellers(ProduitRepository $produitRepository): Response
    {
        // Le nombre de produits BS est à 3 mais peut être modifié
        $bestSellers = $produitRepository->findBestSeller();

        return $this->render('best_sellers.html.twig', [
            'bestSellers' => $bestSellers,
        ]);
    }
}
