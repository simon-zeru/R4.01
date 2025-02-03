<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

// Un service pour manipuler le contenu de la Boutique
//  qui est composée de catégories et de produits stockés "en dur"
class
BoutiqueService
{

    // renvoie toutes les catégories
    public function findAllCategories(): array
    {
        return $this->categories;
    }

    // renvoie la categorie dont id == $idCategorie (null si pas trouvée)
    public function findCategorieById(int $idCategorie): ?object
    {
        $res = array_filter(
            $this->categories,
            function ($categorie) use ($idCategorie) {
                return $categorie->id == $idCategorie;
            }
        );
        return (sizeof($res) === 1) ? $res[array_key_first($res)] : null;
    }

    // renvoie le produit dont id == $idProduit, null si pas trouvé
    public function findProduitById(int $idProduit): ?object
    {
        $res = array_filter(
            $this->produits,
            function ($produit) use ($idProduit) {
                return $produit->id == $idProduit;
            }
        );
        return (sizeof($res) === 1) ? $res[array_key_first($res)] : null;
    }

    // renvoie untableau de produits dont idCategorie == $idCategorie
    public function findProduitsByCategorie(int $idCategorie): array
    {
        return array_filter(
            $this->produits,
            function ($produit) use ($idCategorie) {
                return $produit->idCategorie == $idCategorie;
            }
        );
    }

    // renvoie un tableau de produits dont libelle+texte contient $search
    public function findProduitsByLibelleOrTexte(string $search): array
    {
        return array_filter(
            $this->produits,
            function ($produit) use ($search) {
                return ($search == "" ||
                    mb_strpos(mb_strtolower($produit->libelle) . " " . $produit->texte, mb_strtolower($search)) !== false);
            }
        );
    }

    // constructeur du service : injection des dépendances et tris
    public function __construct(RequestStack $requestStack)
    {
        // Injection du service RequestStack
        //  afin de pouvoir récupérer la "locale" dans la requête en cours
        $this->requestStack = $requestStack;
        // On transforme le JSON en tableaux d'objets PHP
        $this->categories = json_decode(self::LES_CATEGORIES);
        $this->produits = json_decode(self::LES_PRODUITS);
        // On trie le tableau des catégories selon la locale
        usort($this->categories, function ($categorie1, $categorie2) {
            return $this->compareSelonLocale($categorie1->libelle, $categorie2->libelle);
        });
        // On trie le tableau des produits de chaque catégorie selon la locale
        usort($this->produits, function ($categorie1, $categorie2) {
            return $this->compareSelonLocale($categorie1->libelle, $categorie2->libelle);
        });
    }

    ////////////////////////////////////////////////////////////////////////////
    // Attention : \Collator nécessite que le module php "intl" soit activé
    private function compareSelonLocale(string $s1, $s2)
    {
        $collator = new \Collator($this->requestStack->getCurrentRequest()->getLocale());
        return collator_compare($collator, $s1, $s2);
    }

    private $requestStack; // Le service RequestStack qui sera injecté pour accéder à la locale
    private $categories;   // Un tableau trié d'objets standards représentant des catégories
    private $produits;     // Un tableau trié d'objets standards représentant des produits

    // Le catalogue de la boutique, codé en dur dans un tableau associatif
    const LES_CATEGORIES = <<<JSON
    [
        {
            "id" : 1,
            "libelle" : "Fruits",
            "visuel" : "images/categories/fruits.jpg",
            "texte" : "De la passion ou de ton imagination"
        },
        {
            "id" : 2,
            "libelle" : "Légumes",
            "visuel" : "images/categories/legumes.jpg",
            "texte" : "Plus tu en manges, moins tu en es un"
        },
        {
            "id" : 3,
            "libelle" : "Junk Food",
            "visuel" : "images/categories/junk_food.jpg",
            "texte" : "Chère et cancérogène, tu es prévenu(e)"
        },
        {
            "id" : 4,
            "libelle" : "Virus",
            "visuel" : "images/categories/corona.jpg",
            "texte" : "Une opportunité, il faut en profiter"
        }
    ]
JSON;
    const LES_PRODUITS = <<<JSON
    [
        {
            "id" : 1,
            "idCategorie" : 1,
            "libelle" : "Pomme",
            "texte" : "Elle est bonne pour la tienne",
            "visuel" : "images/produits/pommes.jpg",
            "prix" : 3.42
        },
        {
            "id" : 2,
            "idCategorie" : 1,
            "libelle" : "Poire",
            "texte" : "Ici tu n'en es pas une",
            "visuel" : "images/produits/poires.jpg",
            "prix" : 2.11
        },
        {
            "id" : 3,
            "idCategorie" : 1,
            "libelle" : "Pêche",
            "texte" : "Elle va te la donner",
            "visuel" : "images/produits/peche.jpg",
            "prix" : 2.84
        },
        {
            "id" : 4,
            "idCategorie" : 2,
            "libelle" : "Carotte",
            "texte" : "C'est bon pour ta vue",
            "visuel" : "images/produits/carottes.jpg",
            "prix" : 2.90
        },
        {
            "id" : 5,
            "idCategorie" : 2,
            "libelle" : "Tomate",
            "texte" : "Fruit ou Légume ? Légume",
            "visuel" : "images/produits/tomates.jpg",
            "prix" : 1.70
        },
        {
            "id" : 6,
            "idCategorie" : 2,
            "libelle" : "Chou Romanesco",
            "texte" : "Mange des fractales",
            "visuel" : "images/produits/romanesco.jpg",
            "prix" : 1.81
        },
        {
            "id" : 7,
            "idCategorie" : 3,
            "libelle" : "Nutella",
            "texte" : "C'est bon, sauf pour ta santé",
            "visuel" : "images/produits/nutella.jpg",
            "prix" : 4.50
        },
        {
            "id" : 8,
            "idCategorie" : 3,
            "libelle" : "Pizza",
            "texte" : "Y'a pas pire que za",
            "visuel" : "images/produits/pizza.jpg",
            "prix" : 8.25
        },
        {
            "id" : 9,
            "idCategorie" : 3,
            "libelle" : "Oreo",
            "texte" : "Seulement si tu es un smartphone",
            "visuel" : "images/produits/oreo.jpg",
            "prix" : 2.50
        },
        {
            "id" : 10,
            "idCategorie" : 4,
            "libelle" : "Gel Hydroalcoolique",
            "texte" : "Usage interne ou externe",
            "visuel" : "images/produits/gel.jpg",
            "prix" : 100.00
        }, 
        {
            "id" : 11,
            "idCategorie" : 4,
            "libelle" : "Masque FFP 200",
            "texte" : "Passe incognito face aux virus",
            "visuel" : "images/produits/masque.jpg",
            "prix" : 200.0
        }, 
        {
            "id" : 12,
            "idCategorie" : 4,
            "libelle" : "Gants de Protection",
            "texte" : "Reste un touche à tout, avec feeling",
            "visuel" : "images/produits/gants.jpg",
            "prix" : 50.0
        }
    ]
JSON;
}