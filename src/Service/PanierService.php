<?php
namespace App\Service;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Usager;
use Doctrine\ORM\EntityManagerInterface;

class PanierService
{
    ////////////////////////////////////////////////////////////////////////////
    private $session;   // Le service session
    private $boutique;  // Le service boutique
    private $panier;    // Tableau associatif, la clé est un idProduit, la valeur associée est une quantité
                        //   donc $this->panier[$idProduit] = quantité du produit dont l'id = $idProduit
    const PANIER_SESSION = 'panier'; // Le nom de la variable de session pour faire persister $this->panier

    // Constructeur du service
    public function __construct(RequestStack $requestStack, ProduitRepository $produitRepository)
    {
        // Récupération des services session et BoutiqueService
        $this->boutique = $produitRepository;
        $this->session = $requestStack->getSession();
        // Récupération du panier en session s'il existe, init. à vide sinon
        $this->panier = $this->session->get(self::PANIER_SESSION, array());
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
      return count($this->panier);
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
    public function panierToCommande(Usager $usager) : ?Commande {
        // Si le panier est vide, il n'y a rien à convertir en commande
        if (empty($this->panier)) {
            return null;
        }

        // Création d'une nouvelle commande
        $commande = new Commande();
        $commande->setUsager($usager);
        $commande->setDateCreation(new \DateTime());

        $totalCommande = 0;

        // Parcours de chaque produit du panier
        foreach ($this->panier as $idProduit => $quantite) {
            // Récupération du produit via le repository boutique
            $produit = $this->boutique->find($idProduit);
            if (!$produit) {
                // Si le produit n'est pas trouvé, on passe au suivant
                continue;
            }

            // Création d'une ligne de commande pour ce produit
            $ligneCommande = new LigneCommande();
            $ligneCommande->setProduit($produit);
            $ligneCommande->setQuantite($quantite);
            $ligneCommande->setPrix($produit->getPrix());

            // Association de la ligne à la commande
            $commande->addLigneCommande($ligneCommande);

            // Calcul du total de la commande
            $totalCommande += $produit->getPrix() * $quantite;
        }

        $this->setPrix($totalCommande);

        // Persistance de la commande en base de données
        $this->entityManager->persist($commande);
        $this->entityManager->flush();

        // Vidage du panier
        $this->vider();

        return $commande;
    }



}