<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use App\Service\UtilsService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }


    public function findNotAssignedOrders($area)
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.deliveryAddress', 'da')
            ->where('o.shop IS NULL')
            ->andWhere('da.recieverArea = :myArea')
            ->orderBy('o.id', 'DESC')
            ->setParameter('myArea', $area)
            ->getQuery()
            ->getResult();
    }

    public function countOrdersPerDate(array $days)
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id) as count')
            ->addSelect('a.mapName')
            ->innerJoin('o.deliveryAddress', 'da')
            ->innerJoin('da.recieverArea', 'a')
            ->andWhere('DATE_FORMAT(o.createdAt, \'%d %M %Y\') IN (:days)')
            ->groupBy('a.mapName')
            ->setParameters([
                'days' => $days
            ])
            ->getQuery()
            ->getResult();
    }

    public function countOrdersPerMonths(array $months)
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id) as count')
            ->addSelect('a.mapName')
            ->innerJoin('o.deliveryAddress', 'da')
            ->innerJoin('da.recieverArea', 'a')
            ->andWhere('DATE_FORMAT(o.createdAt, \'%M %Y\') IN (:months)')
            ->groupBy('a.mapName')
            ->setParameters([
                'months' => $months
            ])
            ->getQuery()
            ->getResult();
    }

    public function countOrders(array $dates = [])
    {
        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)');
        if (count($dates) > 0) {
            $qb->andWhere('DATE_FORMAT(o.createdAt, \'%d %M %Y\') IN (:days)')
                ->setParameter('days', $dates);
        }
        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 5000000;
        } catch (NonUniqueResultException $e) {
            return 5000000;
        }
    }

    public function countOrdersByShop(array $dates = [], $user = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.shop = :shop')
            ->setParameter('shop', $user);
        if (count($dates) > 0) {
            $qb->andWhere('DATE_FORMAT(o.createdAt, \'%d %M %Y\') IN (:days)')
                ->setParameter('days', $dates);
        }
        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 5000000;
        } catch (NonUniqueResultException $e) {
            return 5000000;
        }
    }

    public function countOrdersAverageSales(array $dates = [])
    {
        $qb = $this->createQueryBuilder('o')
            ->select('AVG(o.totalPrice)');
        if (count($dates) > 0) {
            $qb->andWhere('DATE_FORMAT(o.createdAt, \'%d %M %Y\') IN (:days)')
                ->setParameter('days', $dates);
        }
        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 5000000;
        } catch (NonUniqueResultException $e) {
            return 5000000;
        }
    }

    public function countOrdersAverageSalesByShop(array $dates = [], $user = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->select('AVG(o.totalPrice)')
            ->where('o.shop = :shop')
            ->setParameter('shop', $user);
        if (count($dates) > 0) {
            $qb->andWhere('DATE_FORMAT(o.createdAt, \'%d %M %Y\') IN (:days)')
                ->setParameter('days', $dates);
        }
        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 5000000;
        } catch (NonUniqueResultException $e) {
            return 5000000;
        }
    }

    public function getTotalIncomeByShop($user)
    {
        try {
            return $this->createQueryBuilder('o')
                ->select('SUM(o.totalPrice)')
                ->where('o.shop = :shop')
                ->setParameter('shop', $user)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }


    public function getTotalIncome()
    {
        try {
            return $this->createQueryBuilder('o')
                ->select('SUM(o.totalPrice)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }


    public function findLastWeekOrdersByShop(User $user, array $days)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.shop = :shop')
            ->andWhere('DATE_FORMAT(o.deliveredAt, \'%d %M %Y\') IN (:days)')
            ->setParameters([
                'days' => $days,
                'shop' => $user
            ])
            ->getQuery()
            ->getResult();
    }

    public function findLastWeekOrdersByDriver(User $user, array $days)
    {
        $qb = $this->createQueryBuilder('o');

        $qb->andWhere('o.driver = :driver')
            ->andWhere($qb->expr()->orX(
                'o.status = \'delivered\' AND DATE_FORMAT(o.deliveredAt, \'%d %M %Y\') IN (:days)',
                'o.status = \'shipped\' AND DATE_FORMAT(o.acceptedAt, \'%d %M %Y\') IN (:days)'
            ))
            ->setParameters([
                'days' => $days,
                'driver' => $user
            ]);

        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Order[] Returns an array of Order objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAvailableOrdersForDrivers($user)
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.shop', 's')
            ->innerJoin('o.deliveryAddress', 'da')
            ->where('o.driver IS NULL')
            ->andWhere('da.recieverArea = :area')
            ->setParameter('area', $user->getArea() ?? null)
            ->getQuery()
            ->getResult();
    }

    public function getShippedDeliveredOrdersByDriver($driver, int $nbDays = 0)
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.status != \'new\'')
            ->andWhere('o.driver = :driver');
        if ($nbDays > 0) {
            $now = new \DateTime();
            $date = $now->sub(date_interval_create_from_date_string($nbDays . ' days'));
            $qb->andWhere($qb->expr()->orX('o.acceptedAt >= :date', 'o.deliveredAt >= :date'))
                ->setParameter('date', $date);
        }
        return $qb->setParameter('driver', $driver)
            ->getQuery()
            ->getResult();
    }


    public function countOrdersPerDateDashboard(array $days, $user)
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id) as count, DATE_FORMAT(o.createdAt, \'%d %M %Y\') as day')
            ->where('o.shop = :shop')
            ->andWhere('DATE_FORMAT(o.createdAt, \'%d %M %Y\') IN (:days)')
            ->groupBy('day')
            ->setParameters([
                'shop' => $user,
                'days' => array_keys($days)
            ])
            ->getQuery()
            ->getResult();
    }

    public function countOrdersPerMonthsDashboard(array $months, $user)
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id) as count, DATE_FORMAT(o.createdAt, \'%M %Y\') as month')
            ->where('o.shop = :shop')
            ->andWhere('DATE_FORMAT(o.createdAt, \'%M %Y\') IN (:months)')
            ->groupBy('month')
            ->setParameters([
                'shop' => $user,
                'months' => array_keys($months)
            ])
            ->getQuery()
            ->getResult();
    }


    public function findOrdersByClientAndStatus($client, $delivered)
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.client = :client')
            ->setParameter('client', $client)
            ->andWhere('o.status in (:status)');
        if ($delivered) {
            $qb->setParameter('status', ['delivered']);
        } else {
            $qb->setParameter('status', ['new', 'shipped']);
        }
        return $qb->getQuery()->getResult();


    }

    public function findTodayDeliveryOrders(User $driver)
    {
        return $this->createQueryBuilder('o')
            ->where('o.driver = :driver')
            ->andWhere('DATE_FORMAT(o.deliveryDate, \'%d %m %Y\') = :today')
            ->setParameters([
                'driver' => $driver,
                'today' => (new \DateTime())->format('d m Y')
            ])
            ->getQuery()
            ->getResult();
    }

    public function countAcceptedOrders(?User $driver)
    {
        try {
            return $this->createQueryBuilder('o')
                ->select('COUNT(o.id) as count')
                ->andWhere('o.status = \'new\'')
                ->andWhere('o.acceptedAt IS NOT NULL')
                ->andWhere('o.driver = :driver')
                ->setParameter('driver', $driver)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

    public function countShippedOrders(?User $driver)
    {
        try {
            return $this->createQueryBuilder('o')
                ->select('COUNT(o.id) as count')
                ->andWhere('o.status = \'shipped\'')
                ->andWhere('o.driver = :driver')
                ->setParameter('driver', $driver)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

    public function countDeliveredOrders(?User $driver)
    {
        try {
            return $this->createQueryBuilder('o')
                ->select('COUNT(o.id) as count')
                ->andWhere('o.status = \'delivered\'')
                ->andWhere('o.driver = :driver')
                ->setParameter('driver', $driver)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

    public function getCurrentOrders(?\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        return $this->createQueryBuilder('o')
            ->join('o.client', 'c')
            ->andWhere('c = :user')
            ->andWhere('o.status != \'delivered\'')
            ->andWhere('o.status != \'canceled\'')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function getPreviousOrders(?\Symfony\Component\Security\Core\User\UserInterface $user)
    {

        return $this->createQueryBuilder('o')
            ->join('o.client', 'c')
            ->andWhere('c = :user')
            ->andWhere('o.status = \'delivered\'')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
