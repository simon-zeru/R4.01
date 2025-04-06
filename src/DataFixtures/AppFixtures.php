<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $categories = json_decode(self::LES_CATEGORIES);
        $produits = json_decode(self::LES_PRODUITS);
        $entitesCategories = [];
        foreach ($categories as $cat) {
            $categorie = new Categorie();
            $categorie->setLibelle($cat->libelle);
            $categorie->setVisuel($cat->visuel);
            $categorie->setTexte($cat->texte);
            $manager->persist($categorie);
            $entitesCategories[$cat->id] = $categorie;
        }
        foreach ($produits as $prod) {
            $produit = new Produit();
            $produit->setLibelle($prod->libelle);
            $produit->setVisuel($prod->visuel);
            $produit->setTexte($prod->texte);
            $produit->setPrix($prod->prix);
            $produit->setCategorie($entitesCategories[$prod->idCategorie]);
            $manager->persist($produit);
        }
        $manager->flush();
    }

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
