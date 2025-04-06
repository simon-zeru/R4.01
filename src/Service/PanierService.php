<?php
namespace App\Service;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Usager;

class PanierService
{
    ////////////////////////////////////////////////////////////////////////////
    private $session;   // Le service session
    private $boutique;  // Le service boutique
    private $panier;    // Tableau associatif, la clé est un idProduit, la valeur associée est une quantité
                        //   donc $this->panier[$idProduit] = quantité du produit dont l'id = $idProduit
    private $entityManager;
    const PANIER_SESSION = 'panier'; // Le nom de la variable de session pour faire persister $this->panier

    // Constructeur du service
    public function __construct(RequestStack $requestStack, ProduitRepository $produitRepository, ManagerRegistry $doctrine)
    {
        // Récupération des services session et BoutiqueService
        $this->boutique = $produitRepository;
        $this->session = $requestStack->getSession();
        // Récupération du panier en session s'il existe, init. à vide sinon
        $this->panier = $this->session->get(self::PANIER_SESSION, array());
        $this->entityManager = $doctrine->getManager();
    }

    // Renvoie le montant total du panier
    public function getTotal() : float
    {
        $array = $this->panier;
        $total = 0;
        foreach ($array as $produit) {
            $leProduit = $this->boutique->find($produit['id']);
            $total += $leProduit['prix'] * $produit['quantite'];
        }
        return $total;
    }

    // Renvoie le nombre de produits dans le panier
    public function getNombreProduits() : int
    {
        $totalQuantite = 0; // Initialise le compteur
        // Boucle sur le tableau $this->panier ([idProduit => quantite])
        foreach ($this->panier as $idProduit => $quantite) {
            // Ajoute la quantité de chaque produit au total
            $totalQuantite += $quantite;
        }
        // Retourne la somme totale des quantités
        return $totalQuantite;
    }

    // Ajouter au panier le produit $idProduit en quantite $quantite 
    public function ajouterProduit(int $idProduit, int $quantite = 1) : void
    {
        if (isset($this->panier[$idProduit])) {
            $this->panier[$idProduit] += $quantite;
        } else {
            $this->panier[$idProduit] = $quantite;
        }
        $this->session->set(self::PANIER_SESSION, $this->panier);
    }


    // Enlever du panier le produit $idProduit en quantite $quantite
    public function enleverProduit(int $idProduit, int $quantite = 1) : void
    {
        if (isset($this->panier[$idProduit])) {
            $this->panier[$idProduit] -= $quantite;
            // Si la quantité devient 0 ou moins, on supprime le produit
            if ($this->panier[$idProduit] <= 0) {
                $this->supprimerProduit($idProduit);
            }
        }

        $this->session->set(self::PANIER_SESSION, $this->panier);
    }



    // Supprimer le produit $idProduit du panier
    public function supprimerProduit(int $idProduit) : void
    {
        unset($this->panier[$idProduit]);
        $this->session->set(self::PANIER_SESSION, $this->panier);
    }

    // Vider complètement le panier
    public function vider() : void
    {
      $this->panier = [];
      $this->session->set(self::PANIER_SESSION, []);
    }

    // Renvoie le contenu du panier dans le but de l'afficher
    //   => un tableau d'éléments [ "produit" => un objet produit, "quantite" => sa quantite ]
    public function getContenu() : array
    {
        $contenu = [];

        foreach ($this->panier as $idProduit => $quantite) {
            $produit = $this->boutique->find($idProduit);
            $contenu[] = [
                'produit' => $produit,
                'quantite' => $quantite
            ];
        }

        return $contenu;
    }

    // Crée, pour cet usager, une commande (et ses lignes de commande) à partir du contenu du panier
    //   (s’il n’est pas vide). Le contenu du panier est supprimé à l’issue de ce traitement
    // @return commande l'entité Commande qui vient d'être créée
    public function panierToCommande(Usager $usager): ?Commande
    {
        $panier = $this->getContenu();

        if (empty($panier)) {
            return null;
        }

        $commande = new Commande();
        $commande->setUsager($usager);

        foreach ($panier as $article) {
            $produit = $article["produit"];
            $quantite = $article["quantite"];

            $ligneCommande = new LigneCommande();
            $ligneCommande->setProduit($produit);
            $ligneCommande->setQuantite($quantite);
            $ligneCommande->setCommande($commande);
            $ligneCommande->setPrix($article["produit"]->getPrix() * $quantite);

            $commande->addLigneCommande($ligneCommande);

            $this->entityManager->persist($ligneCommande);
        }

        $commande->setDateCreation(new \DateTime());
        $commande->setValidation(true);
        $this->entityManager->persist($commande);
        $this->entityManager->flush();

        $this->vider();
        return $commande;
    }

}