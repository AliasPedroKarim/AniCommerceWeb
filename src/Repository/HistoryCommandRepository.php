<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\HistoryCommand;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method HistoryCommand|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoryCommand|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoryCommand[]    findAll()
 * @method HistoryCommand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoryCommandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryCommand::class);
    }

    // /**
    //  * @return HistoryCommand[] Returns an array of HistoryCommand objects
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

    /**
     * @param array $criteria
     * @return HistoryCommand|null
     * @throws NonUniqueResultException
     */
    public function findWithUser($criteria){
        return $this->createQueryBuilder('h')
            ->innerJoin(Commande::class, 'c')
            ->innerJoin(Utilisateur::class, 'u')
            ->andWhere('h.idCommand = :commande', 'c.idUtilisateur = :utilisateur')
            ->setParameter('commande', $criteria['idCommand'])
            ->setParameter('utilisateur', $criteria['idUtilisateur'])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /*
    public function findOneBySomeField($value): ?HistoryCommand
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
