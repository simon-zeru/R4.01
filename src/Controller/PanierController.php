<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Repository\UsagerRepository;
use App\Service\PanierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/{_locale}/panier', requirements: ['_locale'=>'%app.supported_locales%'])]
final class PanierController extends AbstractController
{
    #[Route('/', name: 'app_panier_index')]
    public function index(PanierService $panierservice): Response
    {
        $panier = $panierservice->getContenu();

        return $this->render('panier/index.html.twig', [
            'panier' => $panier
        ]);
    }

    #[Route('/ajouter/{idProduit}/{quantite}',
        name: 'app_panier_ajouter',
        requirements: ['idProduit' => '\d+', 'quantite' => '\d+'],
        defaults: ['quantite' => 1],
    )]
    public function ajouter(ProduitRepository $produitRepository, PanierService $panierservice, int $idProduit, int $quantite): Response
    {
        if ($produitRepository->find($idProduit)==null) {
            $this->createNotFoundException("Le produit numéro ".$idProduit." n'existe pas.");
        }
        $panierservice->ajouterProduit($idProduit, $quantite);
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/enlever/{idProduit}/{quantite}',
        name: 'app_panier_enlever',
        requirements: ['idProduit' => '\d+', 'quantite' => '\d+'],
        defaults: ['quantite' => 1],
    )]
    public function enlever(ProduitRepository $produitRepository, PanierService $panierservice, int $idProduit, int $quantite): Response
    {
        if ($produitRepository->find($idProduit)==null) {
            $this->createNotFoundException("Le produit numéro ".$idProduit." n'existe pas.");
        }
        $panierservice->enleverProduit($idProduit, $quantite);
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/supprimer/{idProduit}',
        name: 'app_panier_supprimer',
        requirements: ['idProduit' => '\d+'],
    )]
    public function supprimer(ProduitRepository $produitRepository, PanierService $panierservice, int $idProduit): Response
    {
        if ($produitRepository->find($idProduit)==null) {
            $this->createNotFoundException("Le produit numéro ".$idProduit." n'existe pas.");
        }
        $panierservice->supprimerProduit($idProduit);
        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/vider',
        name: 'app_panier_vider',
    )]
    public function vider(PanierService $panierservice): Response
    {
        $panierservice->vider();
        return $this->redirectToRoute('app_panier_index');
    }

    public function nombreProduits(PanierService $panier): Response {
        $nbProduit = $panier->getNombreProduits();
        return $this->render('panier/nombreProduits.html.twig', [
            'nbProduit' => $nbProduit,
        ]);
    }

    #[Route('/commander',
        name: 'app_panier_commander',
    )]
    public function commander(PanierService $panierservice, UsagerRepository $usagerRepository): Response
    {
        $usager = $this->getUser();

        $commande = $panierservice->panierToCommande($usager);
        $commande->setUsager($usager);

        return $this->render('panier/commande.html.twig', [
                    'commande' => $commande
                ]);
    }

}
