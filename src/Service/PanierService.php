<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\BoutiqueService;

class PanierService
{
    ////////////////////////////////////////////////////////////////////////////
    private $session;   // Le service session
    private $boutique;  // Le service boutique
    private $panier;    // Tableau associatif, la clé est l'idProduit, la valeur la quantité
                        //   donc $this->panier[$idProduit] = quantité du produit dont l'id = $idProduit
    const PANIER_SESSION = 'panier'; // Nom de la variable de session pour stocker le panier

    // Constructeur du service
    public function __construct(RequestStack $requestStack, BoutiqueService $boutique)
    {
        $this->boutique = $boutique;
        $this->session = $requestStack->getSession();
        // Récupération du panier en session s'il existe, sinon initialisation à un tableau vide
        $this->panier = $this->session->get(self::PANIER_SESSION) ?? [];
    }

    // Renvoie le montant total du panier
    public function getTotal() : float
    {
        $total = 0.0;
        foreach ($this->panier as $idArticle => $quantite) {
            $produit = $this->boutique->findProduitById($idArticle);
            if ($produit) {
                $total += $produit->prix * $quantite;
            }
        }
        return $total;
    }

    // Renvoie le nombre total d'articles dans le panier
    public function getNombreProduits() : int
    {
        $nbProduits = 0;
        foreach ($this->panier as $idArticle => $quantite) {
            $nbProduits += $quantite;
        }
        return $nbProduits;
    }

    // Ajouter au panier le produit $idProduit en quantité $quantite
    public function ajouterProduit(int $idProduit, int $quantite = 1) : void
    {
        if (isset($this->panier[$idProduit])) {
            $this->panier[$idProduit] += $quantite;
        } else {
            $this->panier[$idProduit] = $quantite;
        }
        // On sauvegarde le panier en session
        $this->session->set(self::PANIER_SESSION, $this->panier);
    }

    // Enlever du panier le produit $idProduit en quantité $quantite
    public function enleverProduit(int $idProduit, int $quantite = 1) : void
    {
        if (isset($this->panier[$idProduit])) {
            $this->panier[$idProduit] -= $quantite;
            if ($this->panier[$idProduit] <= 0) {
                unset($this->panier[$idProduit]);
            }
            // On sauvegarde le panier en session
            $this->session->set(self::PANIER_SESSION, $this->panier);
        }
    }

    // Supprimer complètement le produit $idProduit du panier
    public function supprimerProduit(int $idProduit) : void
    {
        if (isset($this->panier[$idProduit])) {
            unset($this->panier[$idProduit]);
            // On sauvegarde le panier en session
            $this->session->set(self::PANIER_SESSION, $this->panier);
        }
    }

    // Vider complètement le panier
    public function vider() : void
    {
        $this->panier = [];
        $this->session->set(self::PANIER_SESSION, $this->panier);
    }

    // Renvoie le contenu du panier sous forme de tableau d'éléments :
    // [ "produit" => (objet produit), "quantite" => quantité ]
    public function getContenu() : array
    {
        $contenu = [];
        foreach ($this->panier as $idArticle => $quantite) {
            $produit = $this->boutique->findProduitById($idArticle);
            if ($produit) {
                $contenu[] = [
                    "produit"   => $produit,
                    "quantite"  => $quantite
                ];
            }
        }
        return $contenu;
    }
}
