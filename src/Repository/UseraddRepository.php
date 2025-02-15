<?php

namespace App\Repository;

use App\Entity\Useradd;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Useradd>
 *
 * @method Useradd|null find($id, $lockMode = null, $lockVersion = null)
 * @method Useradd|null findOneBy(array $criteria, array $orderBy = null)
 * @method Useradd[]    findAll()
 * @method Useradd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UseraddRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Useradd::class);
    }

//    /**
//     * @return Useradd[] Returns an array of Useradd objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Useradd
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
