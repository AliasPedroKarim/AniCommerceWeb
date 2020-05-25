<?php

namespace App\Repository;

use App\Entity\HoraireMagasin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HoraireMagasin|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoraireMagasin|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoraireMagasin[]    findAll()
 * @method HoraireMagasin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoraireMagasinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoraireMagasin::class);
    }

    // /**
    //  * @return HoraireMagasin[] Returns an array of HoraireMagasin objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HoraireMagasin
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
