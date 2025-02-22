<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Question>
 *
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

//    /**
//     * @return Question[] Returns an array of Question objects
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

//    public function findOneBySomeField($value): ?Question
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
            ->leftJoin('q.quiz', 'quiz')
            ->addSelect('quiz')
            ->where('LOWER(q.text) LIKE LOWER(:searchTerm)')
            ->orWhere('LOWER(quiz.name) LIKE LOWER(:searchTerm)') // Search by quiz name too
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();
    }

    public function findBySearchAndSort(string $searchTerm, string $sortField, string $sortOrder): array
    {
        $queryBuilder = $this->createQueryBuilder('q')
            ->leftJoin('q.quiz', 'quiz')
            ->addSelect('quiz');

        if (!empty($searchTerm)) {
            $queryBuilder
                ->where('LOWER(q.text) LIKE LOWER(:searchTerm)')
                ->orWhere('LOWER(quiz.name) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        if (in_array($sortField, ['q.text', 'quiz.name'])) {
            $queryBuilder->orderBy($sortField, strtoupper($sortOrder));
        }

        return $queryBuilder->getQuery()->getResult();
    }

}
