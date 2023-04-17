<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'user_list', methods: [Request::METHOD_GET])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $limit = $request->query->getInt('limit', 5);
        $page = $request->query->getInt('page', 1);
        $totalUsers = $userRepository->count([]);
        $users = $userRepository->findBy([], limit: $limit, offset: ($page - 1) * $limit);

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'current_page' => $page,
            'total_pages' => ceil($totalUsers / $limit),
            'limit' => $limit,
        ]);
    }

    #[Route('/user/{id}', name: 'user_show', methods: [Request::METHOD_GET])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
}
