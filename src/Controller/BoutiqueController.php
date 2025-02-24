<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoutiqueController extends AbstractController
{
    #[Route('/{_locale}/boutique', name: 'app_boutique')]
    public function index(CategorieRepository $categorie): Response
    {

        $categories = $categorie->findAll();
        return $this->render('boutique/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/{_locale}/boutique/rayon/{idCategorie}', name: 'app_boutique_rayon')]
    public function showCategorie(CategorieRepository $categorie, int $idCategorie): Response
    {
        $cat = $categorie->find($idCategorie);
        $produits = $cat->getProducts();
        return $this->render('boutique/rayon.html.twig', [
            'categorie' => $cat,
            'produits' => $produits,
        ]);
    }

    #[Route(
        path: '/chercher/{recherche}',
        name: 'app_boutique_chercher',
        requirements: ['recherche' => '.+'], // regexp pour avoir tous les car, / compris
        defaults: ['recherche' => ''])]
    public function chercher(ProduitRepository $produit,
                             string          $recherche): Response
    {
        $produits = $produit->findByLibelleOrTexte($recherche);
        return $this->render('boutique/', [
            'produits' => $produits,
            'recherche' => $recherche,
        ]);
    }

}
