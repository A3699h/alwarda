<?php

namespace App\Repository;

use App\Entity\MessageFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MessageFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageFile[]    findAll()
 * @method MessageFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageFile::class);
    }

    public function findOld()
    {
        $now = new \DateTime();
        $deadLine = $now->add(date_interval_create_from_date_string('10 days'));
        return $this->createQueryBuilder('mf')
            ->where('mf.viewedAt > :deadLine')
            ->setParameter('deadLine', $deadLine)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Image[] Returns an array of Image objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Image
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
