<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }
    public function createFilteredQueryBackoffice(string $searchTerm = '', string $sortBy = ''): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.id_user', 'u')
            ->addSelect('u');

        // Filter by search term (user name or content)
        if ($searchTerm) {
            $queryBuilder->andWhere('u.name LIKE :searchTerm OR u.lastname LIKE :searchTerm OR c.content LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        // Sort by criteria
        switch ($sortBy) {
            case 'newest':
                $queryBuilder->orderBy('c.createdAt', 'DESC');
                break;
            case 'oldest':
                $queryBuilder->orderBy('c.createdAt', 'ASC');
                break;
            default:
                $queryBuilder->orderBy('c.createdAt', 'DESC'); // Default sorting
                break;
        }

        return $queryBuilder;
    }
    //    /**
    //     * @return Comment[] Returns an array of Comment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Comment
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
