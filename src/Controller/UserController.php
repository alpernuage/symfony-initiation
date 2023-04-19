<?php

namespace App\Controller;

use App\Domain\User\UserCreator;
use App\Domain\User\UserInput;
use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

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

    #[Route('/user/{id}', name: 'user_show', requirements: ['id' => Requirement::UUID_V7], methods: [Request::METHOD_GET])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/create', name: 'user_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request, UserCreator $userCreator): Response
    {
        $userInput = new UserInput();

        $form = $this->createForm(UserFormType::class, $userInput);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $userCreator->save($userInput);

            $message = sprintf(
                'New user %s %s created with success.',
                $userInput->firstName,
                $userInput->lastName
            );

            $this->addFlash('success', $message);

            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/create.html.twig', [
            'create_user_form' => $form,
        ]);
    }
}
