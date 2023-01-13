<?php

namespace App\Repository;

use App\Entity\Area;
use App\Entity\DeliveryAddress;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method DeliveryAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryAddress[]    findAll()
 * @method DeliveryAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryAddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryAddress::class);
    }

    // /**
    //  * @return DeliveryAdress[] Returns an array of DeliveryAdress objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DeliveryAdress
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findActiveByUserandByArea(UserInterface $user, Area $area)
    {
        return $this->createQueryBuilder('d')
            ->join('d.client', 'c')
            ->join('d.recieverArea', 'ra')
            ->select('d.id')
            ->addSelect('d.recieverName')
            ->addSelect('d.recieverPhone')
            ->addSelect('d.recieverFullAddress')
            ->andWhere('c = :user')
            ->andWhere('ra = :area')
            ->andWhere('d.active = :trueVal')
            ->setParameters([
                'area' => $area,
                'user' => $user,
                'trueVal' => true
            ])
            ->getQuery()
            ->getResult();
    }
}
