<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

   /**
    * @return Commande[] Returns an array of Commande objects
    */
   public function findByUser($usager): array
   {
       return $this->createQueryBuilder('c')
           ->andWhere('c.usager = :usager')
           ->setParameter('usager', $usager)
           ->orderBy('c.dateCreation', 'DESC')
           ->getQuery()
           ->getResult()
       ;
   }

    //    public function findOneBySomeField($value): ?Commande
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
