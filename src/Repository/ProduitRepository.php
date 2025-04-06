<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * @return Produit[] Returns an array of the three best seller Produit objects
     */
    public function findBestSeller($limit = 3): array
    {
        return $this->createQueryBuilder('p')
            ->select('p AS produit, SUM(l.quantite) AS nbVentes')
            ->join('p.ligneCommandes', 'l')
            ->groupBy('p.id')
            ->orderBy('nbVentes', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

    }

    public function findByLibelleOrTexte(string $recherche): array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p
                  FROM App\Entity\Produit p
                  WHERE p.libelle LIKE :recherche
                  OR p.texte LIKE :recherche
         
        ')->setParameter('recherche', '%' . $recherche . '%');
        return $query->getResult();
    }
}
