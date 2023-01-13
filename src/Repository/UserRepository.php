<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findNotAdmins()
    {
        return $this->createQueryBuilder('u')
            ->where('u.role not in (:adminRole)')
            ->setParameter('adminRole', [
                User::USER_ROLES['superAdmin'],
                User::USER_ROLES['admin']
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function countUsersPerDate(array $days)
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id) as count, DATE_FORMAT(u.createdAt, \'%d %M %Y\') as day')
            ->where('u.role = :clientRole')
            ->andWhere('DATE_FORMAT(u.createdAt, \'%d %M %Y\') IN (:days)')
            ->groupBy('day')
            ->setParameters([
                'clientRole' => User::USER_ROLES['client'],
                'days' => array_keys($days)
            ])
            ->getQuery()
            ->getResult();
    }

    public function countUsersPerMonths(array $months)
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id) as count, DATE_FORMAT(u.createdAt, \'%M %Y\') as month')
            ->where('u.role = :clientRole')
            ->andWhere('DATE_FORMAT(u.createdAt, \'%M %Y\') IN (:months)')
            ->groupBy('month')
            ->setParameters([
                'clientRole' => User::USER_ROLES['client'],
                'months' => array_keys($months)
            ])
            ->getQuery()
            ->getResult();
    }


    public function countClients(array $dates = [])
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.role = :clientRole')
            ->setParameter('clientRole', User::USER_ROLES['client']);
        if (count($dates) > 0) {
            $qb->andWhere('DATE_FORMAT(u.createdAt, \'%d %M %Y\') IN (:days)')
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

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
