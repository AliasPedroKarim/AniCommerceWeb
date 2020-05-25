<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    // /**
    //  * @return Commande[] Returns an array of Commande objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function applyFilter($criteria = []) {
        $query = $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC');

        if(isset($criteria['fields']) && !empty($criteria['fields'])) {
            $query->select('c.id');
            if (is_array($criteria['fields'])) {
                foreach ($criteria['fields'] as $field) {
                    if (property_exists(Commande::class, $field)) {
                        $query->addSelect('c.' . $field);
                    }
                }
            }else{
                $query->addSelect('c.' . $criteria['fields']);
            }
        }

        if (isset($criteria['panier'])) {
            $query
                ->where('c.panier = ' . $criteria['panier']);
        }

        if (isset($criteria['montly']) && !empty($criteria['montly'])) {
            $query
                ->andWhere('YEAR(c.dateLivraison) = YEAR(CURRENT_DATE())')
                ->andWhere('MONTH(c.dateLivraison) = MONTH(CURRENT_DATE())');
        }
        if (isset($criteria['dateLivraisonSort']) && !empty($criteria['dateLivraisonSort'])) {
            $query->orderBy('c.dateLivraison', $criteria['dateLivraisonSort']);
        }

        return $query;
    }

    /**
     * Pe
     *
     * @param $idUtilisateur
     * @param bool $panier
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findWithIdUser($idUtilisateur, $panier = false) {
        $query = $this->createQueryBuilder('c')
            ->select('c, lc')
            ->innerJoin(\App\Entity\LigneCommande::class, 'lc')
            ->andWhere('c.idUtilisateur = :utilisateur', 'c.panier = :panier')
            ->setParameter('utilisateur', $idUtilisateur)
            ->setParameter('panier', $panier)
            ->getQuery();

        return $panier == true ? $query->getOneOrNullResult() : $query->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Commande
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
