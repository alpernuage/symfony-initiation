<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;

/**
 * @template TEntityClass of object
 * @extends ServiceEntityRepository<TEntityClass>
 */
abstract class DeletableEntityRepository extends ServiceEntityRepository
{
    public function findDeletedItemsPaginated(int $currentPage, int $limit): Query
    {
        $queryBuilder = $this->createQueryBuilder('i');

        $queryBuilder->where('i.deletedAt IS NOT NULL')
            ->setMaxResults($limit)
            ->setFirstResult(($currentPage - 1) * $limit);

        return $queryBuilder->getQuery();
    }

    public function getCountQueryBuilderByDeletedStatus(bool $showDeleted): Query
    {
        $queryBuilder = $this->createQueryBuilder('i');
        if ($showDeleted) {
            $queryBuilder->andWhere('i.deletedAt IS NOT NULL');
        } else {
            $queryBuilder->andWhere('i.deletedAt IS NULL');
        }

        return $queryBuilder->getQuery();
    }
}
