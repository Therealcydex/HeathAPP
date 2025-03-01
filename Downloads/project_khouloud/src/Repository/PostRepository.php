<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;use Doctrine\ORM\QueryBuilder;


/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    //    /**
    //     * @return Post[] Returns an array of Post objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findAllQuery()
    {
        return $this->createQueryBuilder('p')
            ->getQuery();
    }

    public function createFilteredQuery(array $filters = [], string $searchTerm = ''):QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->orderBy('p.createdAt', 'DESC');

        // Apply filters
        if (!empty($filters['category'])) {
            $qb->andWhere('p.category IN (:categories)')
                ->setParameter('categories', $filters['category']);
        }

        if (!empty($filters['role'])) {
            $qb->andWhere('a.role IN (:roles)')
                ->setParameter('roles', $filters['role']);
        }

        // Apply search term
        if (!empty($searchTerm)) {
            $qb->andWhere('p.title LIKE :searchTerm OR p.content LIKE :searchTerm OR a.name LIKE :searchTerm OR a.lastname LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'newest':
                    $qb->orderBy('p.createdAt', 'DESC'); // Newest to Oldest
                    break;
                case 'oldest':
                    $qb->orderBy('p.createdAt', 'ASC'); // Oldest to Newest
                    break;

                case 'most-comments':

                  $qb->orderBy('p.commentCount', 'DESC');
                    break;
            }
        }
        if (!empty($filters['time'])) {
            $now = new \DateTime();
            switch ($filters['time']) {
                case 'today':
                    $qb->andWhere('p.createdAt >= :today')
                        ->setParameter('today', $now->format('Y-m-d 00:00:00'));
                    break;
                case 'this-week':
                    $qb->andWhere('p.createdAt >= :startOfWeek')
                        ->setParameter('startOfWeek', $now->modify('last Monday')->format('Y-m-d 00:00:00'));
                    break;
                case 'this-month':
                    $qb->andWhere('p.createdAt >= :startOfMonth')
                        ->setParameter('startOfMonth', $now->modify('first day of this month')->format('Y-m-d 00:00:00'));
                    break;
            }
        }
        return $qb;
    }
    // Count filtered results
    public function countFilteredResults(array $filters = [], string $searchTerm = ''): int
    {
        $qb = $this->createFilteredQuery($filters, $searchTerm)
            ->select('COUNT(DISTINCT p.id)'); // Count distinct posts

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function createFilteredQueryBackoffice(string $searchTerm = '', string $sortBy = ''): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.author', 'a')
            ->addSelect('a');

        // Filter by search term (author name, title, or content)
        if ($searchTerm) {
            $queryBuilder->andWhere('a.name LIKE :searchTerm OR a.lastname LIKE :searchTerm OR p.title LIKE :searchTerm OR p.content LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        // Sort by criteria
        switch ($sortBy) {
            case 'newest':
                $queryBuilder->orderBy('p.createdAt', 'DESC');
                break;
            case 'oldest':
                $queryBuilder->orderBy('p.createdAt', 'ASC');
                break;
            case 'most-comments':
                $queryBuilder->orderBy('p.commentCount', 'DESC');

                break;
            default:
                $queryBuilder->orderBy('p.createdAt', 'DESC'); // Default sorting
                break;
        }

    return $queryBuilder;
    }
}
