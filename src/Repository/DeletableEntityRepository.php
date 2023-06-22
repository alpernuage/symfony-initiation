<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;

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
}
