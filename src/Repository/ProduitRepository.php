<?php

namespace App\Repository;

use App\Entity\AssocierCategorie;
use App\Entity\Categorie;
use App\Entity\Image;
use App\Entity\Presenter;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    // /**
    //  * @return Produit[] Returns an array of Produit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
        // ->resetDQLPart('select')
    public function apiSimpleFormat() {
        return $this->createQueryBuilder('pr')
            ->select('pr.id', 'pr.libelle', 'pr.stock')

            //->leftJoin(Presenter::class, 'p', 'WITH', 'pr.id = p.idProduit')
            //->leftJoin(Image::class, 'i', 'WITH', 'i.id = p.idImage')
            //->select('DISTINCT(pr.id)', 'pr.libelle', 'pr.stock')
            //->addSelect('i.cheminImage')

            ->orderBy('pr.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param array $criteria
     * @return QueryBuilder
     */
    public function applyFilter($criteria = []) {
        $queryBuilder = $this->createQueryBuilder('pr');

        // Ici commence les filtres
        if (isset($criteria['wordKeys']) && !empty($criteria['wordKeys'])) {
            $queryBuilder
                ->orWhere('pr.libelle LIKE :wordKeys', 'pr.description LIKE :wordKeys')
                ->setParameter('wordKeys', '%' . $criteria['wordKeys'] . '%');
        }

        if (isset($criteria['categories']) && !empty($criteria['categories']) && $criteria['categories'] instanceof ArrayCollection && !$criteria['categories']->isEmpty()) {
            $queryBuilder
                ->innerJoin(AssocierCategorie::class, 'ac')
                ->innerJoin(Categorie::class, 'c')

                // NON MAIS SERIEUX
                ->andWhere('pr.id = ac.idProduit', 'c.id = ac.idCategorie')
                ->andWhere('c.id IN (:ids)')
                ->setParameter('ids', $criteria['categories']->map(function ($object) {
                    return $object->getId();
                })->getValues());
        }

        if (isset($criteria['priceMin']) && !empty($criteria['priceMin'])) {
            $queryBuilder
                ->andWhere('pr.prixHt >= :min')
                ->setParameter('min', $criteria['priceMin']);
        }

        if (isset($criteria['priceMax']) && !empty($criteria['priceMax'])) {
            $queryBuilder
                ->andWhere('pr.prixHt <= :max')
                ->setParameter('max', $criteria['priceMax']);
        }


        if (isset($criteria['magasins']) && !empty($criteria['magasins'])) {
            $queryBuilder
                ->andWhere('pr.idMagasin IN (:ids)')
                ->setParameter('ids', $criteria['magasins']);
        }

        if (isset($criteria['limit']) && !empty($criteria['limit'])) {
            $queryBuilder
                ->setMaxResults($criteria['limit']);
        }

        // Ici fini le filtre

        return $queryBuilder
            ->orderBy('pr.id', 'ASC');
    }

    /*
    public function findOneBySomeField($value): ?Produit
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
