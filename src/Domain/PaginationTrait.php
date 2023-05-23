<?php

namespace App\Domain;

use Symfony\Component\HttpFoundation\Request;

trait PaginationTrait
{
    protected function getPaginationData(Request $request, object $repository, $defaultLimit = 10): array
    {
        $limit = $request->query->getInt('limit', $defaultLimit);

        if ($limit <= 0) {
            $limit = $defaultLimit;
        }

        $page = $request->query->getInt('page', 1);

        if ($page <= 0) {
            $page = 1;
        }

        $objectCount = $repository->count([]);

        return [
            'current_page' => $page,
            'pages_count' => ceil($objectCount / $limit),
            'limit' => $limit,
        ];
    }
}
