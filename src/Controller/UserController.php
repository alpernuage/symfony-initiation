<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Route('/users', name: 'user-list', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limit = $request->query->getInt('limit', 5);
        $page = $request->query->getInt('page', 1);
        $totalUsers = $this->userRepository->count([]);
        $users = $this->userRepository->findBy([], limit: $limit, offset: ($page - 1) * $limit);

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'current_page' => $page,
            'total_pages' => ceil($totalUsers / $limit),
            'limit' => $limit,
        ]);
    }
}
