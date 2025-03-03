<?php

namespace App\Repository;

use App\Entity\Answer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Answer>
 *
 * @method Answer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Answer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Answer[]    findAll()
 * @method Answer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        if (empty($searchTerm)) {
            return $this->findAll();
        }

        return $this->createQueryBuilder('a')
            ->leftJoin('a.question', 'q')
            ->addSelect('q')
            ->where('LOWER(a.text) LIKE LOWER(:searchTerm)')
            ->orWhere('LOWER(q.text) LIKE LOWER(:searchTerm)') // Search by question text too
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();
    }

    public function findBySearchAndSort(string $searchTerm, string $sortField, string $sortOrder): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->leftJoin('a.question', 'q')
            ->addSelect('q');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->where('LOWER(a.text) LIKE LOWER(:searchTerm)')
                ->orWhere('LOWER(q.text) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        if (in_array($sortField, ['a.text', 'q.text', 'a.isCorrect'])) {
            $queryBuilder->orderBy($sortField, strtoupper($sortOrder));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findBySearchAndSortQuery(string $searchTerm, string $sortField, string $sortOrder)
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->leftJoin('a.question', 'q')
            ->addSelect('q');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->where('LOWER(a.text) LIKE LOWER(:searchTerm)')
                ->orWhere('LOWER(q.text) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        if (in_array($sortField, ['a.text', 'q.text', 'a.isCorrect'])) {
            $queryBuilder->orderBy($sortField, strtoupper($sortOrder));
        }

        return $queryBuilder->getQuery();
    }


//    /**
//     * @return Answer[] Returns an array of Answer objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Answer
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
