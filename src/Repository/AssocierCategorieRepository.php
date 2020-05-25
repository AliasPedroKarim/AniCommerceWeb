<?php

namespace App\Repository;

use App\Entity\AssocierCategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AssocierCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssocierCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssocierCategorie[]    findAll()
 * @method AssocierCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssocierCategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssocierCategorie::class);
    }

    // /**
    //  * @return AssocierCategorie[] Returns an array of AssocierCategorie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AssocierCategorie
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
