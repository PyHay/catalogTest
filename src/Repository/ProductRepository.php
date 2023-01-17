<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public const PRODUCTS_PER_PAGE = 2;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getProductPaginator(int $offset, ?string $filter, ?string $sort): Paginator
    {
        $query = $this->createQueryBuilder('c')
            ->setMaxResults(self::PRODUCTS_PER_PAGE)
            ->setFirstResult($offset);

        if ($sort){
            $query->orderBy("c.$sort", 'ASC')
                ->getQuery();
        } elseif($filter) {
            $query->andWhere("c.category = :filter")
                ->setParameter('filter', $filter)
                ->getQuery();
        } else {
            $query->getQuery();
        }

        return new Paginator($query);
    }

    public function sortByColumn($column): Query
    {
        return $this->createQueryBuilder('p')
            ->orderBy("p.$column", 'DESC')
            ->getQuery();
    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
