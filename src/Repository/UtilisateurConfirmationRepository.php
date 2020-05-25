<?php

namespace App\Repository;

use App\Entity\UtilisateurConfirmation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UtilisateurConfirmation|null find($id, $lockMode = null, $lockVersion = null)
 * @method UtilisateurConfirmation|null findOneBy(array $criteria, array $orderBy = null)
 * @method UtilisateurConfirmation[]    findAll()
 * @method UtilisateurConfirmation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UtilisateurConfirmationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UtilisateurConfirmation::class);
    }

    // /**
    //  * @return UtilisateurConfirmation[] Returns an array of UtilisateurConfirmation objects
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
    public function findOneBySomeField($value): ?UtilisateurConfirmation
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
