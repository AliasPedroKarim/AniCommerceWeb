<?php

namespace App\Repository;

use App\Entity\UtiliserPromo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UtiliserPromo|null find($id, $lockMode = null, $lockVersion = null)
 * @method UtiliserPromo|null findOneBy(array $criteria, array $orderBy = null)
 * @method UtiliserPromo[]    findAll()
 * @method UtiliserPromo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UtiliserPromoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UtiliserPromo::class);
    }

    // /**
    //  * @return UtiliserPromo[] Returns an array of UtiliserPromo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UtiliserPromo
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
