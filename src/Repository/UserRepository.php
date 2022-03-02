<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

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

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(User $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(User $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }


    public function countAll(): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)');
        
        return $qb->getQuery()->getSingleScalarResult();
    }



    public function averageAge(): int
    {
        $query = $this->createQueryBuilder('u')
            ->select('avg(DATE_DIFF(:now, u.birthdate))')
            ->setParameter('now', new \DateTime(), Types::DATETIME_MUTABLE);

        $qb = $query->getQuery();
        return floor($qb->getSingleScalarResult())/365; // le résultat renvoie une moyenne en nombre de jours, j'ai divisé par 365 pour l'avoir en années et ai arrondi avec floor()
    }

    public function nbBetweenUsers(): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->andWhere('DATE_DIFF(:now, u.birthdate) >= 7300') // 7300 correspond à 20 convertit en nombre de jours
            ->andWhere('DATE_DIFF(:now, u.birthdate) <= 18250') // la même chose pour 50 ans
            ->setParameter('now', new \DateTime(), Types::DATETIME_MUTABLE);

        return $qb->getQuery()->getSingleScalarResult();
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
