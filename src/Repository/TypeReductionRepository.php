<?php

namespace App\Repository;

use App\Entity\TypeReduction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TypeReduction|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeReduction|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeReduction[]    findAll()
 * @method TypeReduction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeReductionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeReduction::class);
    }

    // /**
    //  * @return TypeReduction[] Returns an array of TypeReduction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TypeReduction
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
