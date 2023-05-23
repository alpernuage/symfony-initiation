<?php

namespace App\Controller;

use App\Domain\PaginationTrait;
use App\Repository\HomeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    use PaginationTrait;

    #[Route('/homes', name: 'home_list', methods: [Request::METHOD_GET])]
    public function index(Request $request, HomeRepository $homeRepository): Response
    {
        $paginationData = $this->getPaginationData($request, $homeRepository);
        $homes = $homeRepository->findBy(
            criteria: [],
            limit: $paginationData['limit'],
            offset: ($paginationData['current_page'] - 1) * $paginationData['limit']
        );

        return $this->render('home/index.html.twig', [
            'homes' => $homes,
            'current_page' => $paginationData['current_page'],
            'pages_count' => $paginationData['pages_count'],
            'limit' => $paginationData['limit'],
        ]);
    }
}
