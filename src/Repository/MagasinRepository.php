<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\Magasin;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Magasin|null find($id, $lockMode = null, $lockVersion = null)
 * @method Magasin|null findOneBy(array $criteria, array $orderBy = null)
 * @method Magasin[]    findAll()
 * @method Magasin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagasinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Magasin::class);
    }

    // /**
    //  * @return Magasin[] Returns an array of Magasin objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /**
     * @param array $criteria
     * @return QueryBuilder
     */
    public function applyFilter($criteria = []) {
        $queryBuilder = $this->createQueryBuilder('m');

        // Default
        $queryBuilder
            ->orderBy('m.id', 'ASC');

        // Ici commence les filtres
        if (isset($criteria['wordKeys']) && !empty($criteria['wordKeys'])) {
            $queryBuilder
                ->orWhere('m.nom LIKE :wordKeys', 'm.courriel LIKE :wordKeys')
                ->setParameter('wordKeys', '%' . $criteria['wordKeys'] . '%');
        }

        if (isset($criteria['magasinCA']) && !empty($criteria['magasinCA'])) {
            $queryBuilder
                ->addSelect('SUM(lc.prixUnitaire * lc.quantite) AS total')
                ->innerJoin(Produit::class, 'p')
                ->innerJoin(LigneCommande::class, 'lc')
                ->innerJoin(Commande::class, 'c')

                // EUH OK WHAT THE FU****
                ->andWhere('m.id = p.idMagasin', 'p.id = lc.idProduit', 'lc.idCommande = c.id')
                ->andWhere('c.panier = 0')
                ->groupBy('m.id')
                ->orderBy('total', 'DESC');
        }

        if (isset($criteria['limit']) && !empty($criteria['limit'])) {
            $queryBuilder
                ->setMaxResults($criteria['limit']);
        }

        // Ici fini le filtre

        return $queryBuilder;
    }


    /*
    public function findOneBySomeField($value): ?Magasin
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
