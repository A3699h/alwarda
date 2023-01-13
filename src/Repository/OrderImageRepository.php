<?php

namespace App\Repository;

use App\Entity\OrderImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderImage[]    findAll()
 * @method OrderImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderImage::class);
    }

    // /**
    //  * @return OrderImage[] Returns an array of OrderImage objects
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
    public function findOneBySomeField($value): ?OrderImage
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
