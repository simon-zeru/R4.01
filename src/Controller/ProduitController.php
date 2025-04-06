<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/{_locale}/produit', requirements: ['_locale'=>'%app.supported_locales%'])]
final class ProduitController extends AbstractController
{
    #[Route('/{idProduit}', name: 'app_produit', requirements:['idProduit' => '\d+'])]
    public function detail(ProduitRepository $produitRepository, int $idProduit): Response
    {
        $produit = $produitRepository->find($idProduit);

        if ($produit==null) {
            $this->createNotFoundException("Le produit numéro ".$idProduit." n'existe pas.");
        }

        return $this->render('produit/detail.html.twig', [
            'produit' => $produit, 
        ]);
    }
}
