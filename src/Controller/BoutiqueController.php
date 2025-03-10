<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Service\BoutiqueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoutiqueController extends AbstractController
{
    #[Route('/{_locale}/boutique', name: 'app_boutique', requirements: ['_locale' => '%app.supported_locales%'])]
    public function index(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();
        return $this->render('boutique/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/{_locale}/boutique/rayon/{idCategorie}', name: 'app_boutique_rayon', requirements: ['_locale' => '%app.supported_locales%'],)]
    public function rayon(int $idCategorie, CategorieRepository $categorieRepository) : Response
    {
        $categorie = $categorieRepository->find($idCategorie);
        $products = $categorie->getProduits();
        return $this->render('boutique/product.html.twig', [
            'categorie' => $categorie,
            'products' => $products,
        ]);
    }

    #[Route('/{_locale}/boutique/chercher/{recherche}', name: 'app_boutique_cherche', requirements: ['recherche' => '.+'], defaults: ['recherche' => ''])]
    public function chercher(ProduitRepository $produitRepository, string $recherche) : Response
    {
        $products = $produitRepository->findByLibelleOrTexte($recherche);
        return $this->render('boutique/chercher.html.twig', [
            'products' => $products,
            'recherche' => $recherche,
        ]);
    }


}
