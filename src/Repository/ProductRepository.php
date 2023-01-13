<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Product::class);
        $this->paginator = $paginator;
    }

    public function getStockedProducts(Category $category = null, array $filters = [])
    {
        $qb = $this->createQueryBuilder('p')
            // ->innerJoin('p.users', 'u')
            ->where('p.enabled = :trueVal')
            ->andWhere('p.visible = :trueVal')
            ->setParameter('trueVal', true);
        if ($category) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }
        if ($filters['color']) {
            $qb->join('p.color', 'c')
                ->andWhere('c.code = :color')
                ->setParameter('color', $filters['color']);
        }
        if ($filters['type']) {
            $qb->andWhere('p.type = :type')
                ->setParameter('type', $filters['type']);
        }
        if ($filters['minPrice']) {
            $qb->andWhere('(p.cost + p.benefit) >= :minPrice')
                ->setParameter('minPrice', $filters['minPrice']);
        }
        if ($filters['maxPrice']) {
            $qb->andWhere('(p.cost + p.benefit) <= :maxPrice')
                ->setParameter('maxPrice', $filters['maxPrice']);
        }
        if ($filters['categoryId']) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $filters['categoryId']);
        }
        if ($filters['package']) {
            $qb->andWhere('p.package = :trueVal')
                ->andWhere('p.package IS NOT NUll')
                ->setParameter('trueVal', true);
        }
        if ($filters['search']) {
            $search = $filters['search'];
            $qb->andWhere($qb->expr()->orX(
                "p.name LIKE '%" . $search . "%'",
                "p.description LIKE '%" . $search . "%'",
                "p.longDescription LIKE '%" . $search . "%'",
                "p.longDescriptionAr LIKE '%" . $search . "%'"
            ));
        }
        return $qb->getQuery()->getResult();
    }

    public function getStockedProductsLimited(Category $category = null, int $limit = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.users', 'u')
            ->where('p.enabled = :trueVal')
            ->andWhere('p.visible = :trueVal')
            ->setParameter('trueVal', true);
        if ($category) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }
        if ($limit) {
            $qb->setMaxResults($limit + 1);
        }
        return $qb->getQuery()->getResult();
    }

    public function findProductsNotProvidedByShop($ids)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->andWhere($qb->expr()->eq('p.enabled', ':trueBoolean'))
            ->andWhere($qb->expr()->notIn('p.id', ':currentUserProducts'))
            ->setParameters([
                'trueBoolean' => true,
                'currentUserProducts' => $ids
            ])
            ->getQuery()
            ->getResult();
    }

    public function getMostSoldProducts(int $limit = 0)
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('COUNT(od.id) as count')
            ->innerJoin('p.orderDetails', 'od')
            ->groupBy('p')
            ->orderBy('count', 'DESC');
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        return $qb->getQuery()->getResult();
    }

    public function getMinMaxPrice()
    {
        return $this->createQueryBuilder('p')
            ->select('MIN(p.cost + p.benefit) as min')
            ->addSelect('MAX(p.cost + p.benefit) as max')
            ->getQuery()
            ->getSingleResult();
    }

    public function getStockedProductsFiltredPaginated(array $filters)
    {
        $minPrice = $filters['minPrice'];
        $maxPrice = $filters['maxPrice'];
        $sortBy = $filters['sortBy'];
        $color = $filters['color'];
        $page = $filters['page'];
        $results = 15;

        $qb = $this->createQueryBuilder('p')
            ->addSelect('(p.cost + p.benefit) as price')
            ->andWhere('p.enabled = :trueVal')
            ->andWhere('p.visible = :trueVal')
            ->andWhere('p.category = :category')
            ->andWhere('(p.cost + p.benefit) >= :minPrice')
            ->andWhere('(p.cost + p.benefit) <= :maxPrice')
            ->setParameters([
                'trueVal' => true,
                'category' => $filters['category'],
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice
            ]);

        switch ($sortBy) {
            case 'priceAsc':
                $qb->orderBy('price', 'ASC');
                break;
            case 'priceDesc':
                $qb->orderBy('price', 'DESC');
                break;
        }

        if ($color != '') {
            $qb->join('p.color', 'c')
                ->andWhere("c.code = :color")
                ->setParameter('color', $color);
        }
//        dd($this->paginator->paginate($qb->getQuery(), $page, $results));
        return $this->paginator->paginate($qb->getQuery(), $page, $results);
    }


    public function getMaxPrice()
    {
        try {
            return $this->createQueryBuilder('p')
                ->select('MAX(p.cost + p.benefit)')
                ->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }
}
