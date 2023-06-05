<?php

namespace App\Controller;

use App\Domain\PaginationTrait;
use App\Domain\SuccessMessageTrait;
use App\Domain\User\UserCreatorInterface;
use App\Domain\User\UserEditorInterface;
use App\Domain\User\UserInput;
use App\Domain\User\UserRemoverInterface;
use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    use PaginationTrait;
    use SuccessMessageTrait;

    #[Route('/{_locale}/users', name: 'user_list', requirements: ['_locale' => '%app.supported_locales%'], methods: [Request::METHOD_GET])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $paginationData = $this->getPaginationData($request, $userRepository);
        $users = $userRepository->findBy(
            criteria: [],
            limit: $paginationData['limit'],
            offset: ($paginationData['current_page'] - 1) * $paginationData['limit']
        );

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'current_page' => $paginationData['current_page'],
            'pages_count' => $paginationData['pages_count'],
            'limit' => $paginationData['limit'],
        ]);
    }

    #[Route('/{_locale}/user/{id}', name: 'user_show', requirements: ['id' => Requirement::UUID_V7, '_locale' => '%app.supported_locales%'], methods: [Request::METHOD_GET])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{_locale}/user/create', name: 'user_create', requirements: ['_locale' => '%app.supported_locales%'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request, UserCreatorInterface $userCreator, TranslatorInterface $translator): Response
    {
        $userInput = new UserInput();

        $form = $this->createForm(UserFormType::class, $userInput);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $userCreator->create($userInput);
            $this->setTranslator($translator);
            $message = sprintf(
                $this->getSuccessMessage('create', 'user'),
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

    #[Route('/{_locale}/user/delete/{id}', name: 'user_delete', requirements: ['_locale' => '%app.supported_locales%'], methods: [Request::METHOD_POST])]
    public function remove(User $user, Request $request, UserRemoverInterface $userRemover, TranslatorInterface $translator): Response
    {
        /** @var string $submittedToken */
        $submittedToken = $request->request->get('token', "");

        if (!$this->isCsrfTokenValid('delete-item', $submittedToken)) {
            throw new InvalidCsrfTokenException("Invalid CSRF token.");
        }

        $userRemover->remove($user);
        $this->setTranslator($translator);
        $this->addFlash(
            'success',
            sprintf(
                $this->getSuccessMessage('delete', 'user'),
                $user->getFirstName(),
                $user->getLastName(),
            )
        );

        return $this->redirectToRoute('user_list');
    }

    #[Route('/{_locale}/user/edit/{id}', name: 'user_edit', requirements: ['_locale' => '%app.supported_locales%'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(User $user, Request $request, UserEditorInterface $userEditor, TranslatorInterface $translator): Response
    {
        $userInput = UserInput::createInputForUpdate($user);

        $form = $this->createForm(UserFormType::class, $userInput);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $userEditor->edit($user, $userInput);
            $this->setTranslator($translator);
            $message = sprintf(
                $this->getSuccessMessage('edit', 'user'),
                $userInput->firstName,
                $userInput->lastName
            );

            $this->addFlash('success', $message);

            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'edit_user_form' => $form,
        ]);
    }
}
