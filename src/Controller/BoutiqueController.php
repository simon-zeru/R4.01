<?php

namespace App\Controller;

use App\Service\BoutiqueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoutiqueController extends AbstractController
{
    #[Route('/boutique', name: 'app_boutique')]
    public function index(BoutiqueService $boutiqueService): Response
    {
        $categories=$boutiqueService->findAllCategories();
        return $this->render('boutique/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/boutique/rayon/{idCategorie}', name: 'app_boutique_rayon')]
    public function showCategorie(BoutiqueService $boutiqueService, int $idCategorie): Response
    {
        $categorie=$boutiqueService->findCategorieById($idCategorie);
        $produits=$boutiqueService->findProduitsByCategorie($idCategorie);
        return $this->render('boutique/rayon.html.twig', [
            'categorie' => $categorie,
            'produits' => $produits,

        ]);
    }
}
