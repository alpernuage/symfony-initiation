<?php

namespace App\Domain;

use App\Repository\DeletableEntityRepository;
use Symfony\Component\HttpFoundation\Request;

trait PaginationTrait
{
    /**
     * @template TEntityClass of object
     *
     * @param DeletableEntityRepository<TEntityClass> $repository
     * @return array<string, mixed>
     */
    protected function getPaginationData(Request $request, DeletableEntityRepository $repository, bool $showDeleted = false, int $defaultLimit = 10): array
    {
        $limit = $request->query->getInt('limit', $defaultLimit);

        if ($limit <= 0) {
            $limit = $defaultLimit;
        }

        $page = $request->query->getInt('page', 1);

        if ($page <= 0) {
            $page = 1;
        }

        $queryBuilder = $repository->getCountQueryBuilderByDeletedStatus($showDeleted);
        $objectCount = count((array)$queryBuilder->getResult());

        return [
            'current_page' => $page,
            'pages_count' => ceil($objectCount / $limit),
            'limit' => $limit,
        ];
    }
}
