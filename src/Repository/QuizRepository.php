<?php

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quiz>
 *
 * @method Quiz|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quiz|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quiz[]    findAll()
 * @method Quiz[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }

//    /**
//     * @return Quiz[] Returns an array of Quiz objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('q.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Quiz
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findBySearchTerm(string $searchTerm): array
    {
        if (empty($searchTerm)) {
            return $this->findAll();
        }

        return $this->createQueryBuilder('q')
            ->where('LOWER(q.name) LIKE LOWER(:searchTerm)')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();
    }

    public function findBySearchAndSort(string $searchTerm, string $sortField, string $sortOrder): array
    {
        $queryBuilder = $this->createQueryBuilder('q');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->where('LOWER(q.name) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        if (in_array($sortField, ['name', 'type'])) {
            $queryBuilder->orderBy("q.$sortField", strtoupper($sortOrder));
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
