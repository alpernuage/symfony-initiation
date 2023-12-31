<?php

namespace App\Controller;

use App\Domain\Home\HomeCreatorInterface;
use App\Domain\Home\HomeEditorInterface;
use App\Domain\Home\HomeInput;
use App\Domain\Home\HomeRemoverInterface;
use App\Domain\Home\HomeRestorerInterface;
use App\Domain\PaginationTrait;
use App\Domain\SuccessMessageTrait;
use App\Entity\Home;
use App\Form\HomeFormType;
use App\Repository\HomeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HomeController extends AbstractController
{
    use PaginationTrait;
    use SuccessMessageTrait;

    #[Route('/{_locale}/homes', name: 'home_list', requirements: ['_locale' => '%app.supported_locales%'], methods: [Request::METHOD_GET])]
    public function index(Request $request, HomeRepository $homeRepository): Response
    {
        $paginationData = $this->getPaginationData($request, $homeRepository);
        $homes = $homeRepository->findBy(
            criteria: ['deletedAt' => null],
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

    #[Route('/{_locale}/deleted_homes', name: 'deleted_homes', requirements: ['_locale' => '%app.supported_locales%'], methods: [Request::METHOD_GET])]
    public function showDeletedHomes(Request $request, HomeRepository $homeRepository): Response
    {
        $paginationData = $this->getPaginationData($request, $homeRepository, showDeleted: true);
        $currentPage = intval($paginationData['current_page']);
        $limit = intval($paginationData['limit']);
        $queryBuilder = $homeRepository->findDeletedHomesPaginated($currentPage, $limit);
        $homes = $queryBuilder->getResult();

        return $this->render('home/deleted_homes.html.twig', [
            'homes' => $homes,
            'current_page' => $paginationData['current_page'],
            'pages_count' => $paginationData['pages_count'],
            'limit' => $paginationData['limit'],
        ]);
    }

    #[Route('/{_locale}/home/{id}', name: 'home_show', requirements: ['id' => Requirement::UUID_V7, '_locale' => '%app.supported_locales%'], methods: [Request::METHOD_GET])]
    public function show(Request $request, Home $home): Response
    {
        return $this->render('home/show.html.twig', [
            'home' => $home,
            'countryName' => $request->attributes->get('countryName')
        ]);
    }

    #[Route('/{_locale}/home/create', name: 'home_create', requirements: ['_locale' => '%app.supported_locales%'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request, HomeCreatorInterface $homeCreator, TranslatorInterface $translator): Response
    {
        $homeInput = new HomeInput();

        $form = $this->createForm(HomeFormType::class, $homeInput);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $home = $homeCreator->create($homeInput);
            $this->setTranslator($translator);
            $message = sprintf(
                $this->getSuccessMessage('create', 'home'),
                $homeInput->address,
                $homeInput->city
            );

            $this->addFlash('success', $message);

            return $this->redirectToRoute('home_show', ['id' => $home->getId()]);
        }

        return $this->render('home/create.html.twig', [
            'create_home_form' => $form,
        ]);
    }

    #[Route('/{_locale}/home/edit/{id}', name: 'home_edit', requirements: ['_locale' => '%app.supported_locales%'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Home $home, Request $request, HomeEditorInterface $homeEditor, TranslatorInterface $translator): Response
    {
        $homeInput = HomeInput::createInputForUpdate($home);

        $form = $this->createForm(HomeFormType::class, $homeInput);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $homeEditor->edit($home, $homeInput);
            $this->setTranslator($translator);
            $message = sprintf(
                $this->getSuccessMessage('edit', 'home'),
                $homeInput->address,
                $homeInput->city
            );

            $this->addFlash('success', $message);

            return $this->redirectToRoute('home_show', ['id' => $home->getId()]);
        }

        return $this->render('home/edit.html.twig', [
            'edit_home_form' => $form,
        ]);
    }

    #[Route('/{_locale}/home/delete/{id}', name: 'home_delete', requirements: ['_locale' => '%app.supported_locales%'], methods: [Request::METHOD_POST])]
    public function remove(Home $home, Request $request, HomeRemoverInterface $homeRemover, TranslatorInterface $translator): Response
    {
        $submittedToken = $request->request->get('token');

        if (!$this->isCsrfTokenValid('soft-delete-item', $submittedToken)) {
            throw new InvalidCsrfTokenException("Invalid CSRF token.");
        }

        $homeRemover->remove($home);
        $this->setTranslator($translator);
        $this->addFlash(
            'success',
            sprintf(
                $this->getSuccessMessage('delete', 'home'),
                $home->getAddress(),
                $home->getCity(),
            )
        );

        return $this->redirectToRoute('home_list');
    }

    #[Route('/{_locale}/home/hard_delete/{id}', name: 'home_hard_delete', requirements: ['_locale' => '%app.supported_locales%'], methods: [Request::METHOD_POST])]
    public function hardRemove(Home $home, Request $request, HomeRemoverInterface $homeRemover, TranslatorInterface $translator): Response
    {
        /** @var string $submittedToken */
        $submittedToken = $request->request->get('token', "");

        if (!$this->isCsrfTokenValid('hard-delete-item', $submittedToken)) {
            throw new InvalidCsrfTokenException("Invalid CSRF token.");
        }

        $homeRemover->hardRemove($home);
        $this->setTranslator($translator);
        $this->addFlash(
            'success',
            sprintf(
                $this->getSuccessMessage('delete', 'home'),
                $home->getAddress(),
                $home->getCity(),
            )
        );

        return $this->redirectToRoute('home_list');
    }

    #[Route('/{_locale}/home/restore/{id}', name: 'home_restore', requirements: ['_locale' => '%app.supported_locales%'], methods: [Request::METHOD_POST])]
    public function restore(Home $home, Request $request, HomeRestorerInterface $homeRestorer, TranslatorInterface $translator): Response
    {
        /** @var string $submittedToken */
        $submittedToken = $request->request->get('token', "");

        if (!$this->isCsrfTokenValid('restore-item', $submittedToken)) {
            throw new InvalidCsrfTokenException("Invalid CSRF token.");
        }

        $homeRestorer->restore($home);
        $this->setTranslator($translator);
        $this->addFlash(
            'success',
            sprintf(
                $this->getSuccessMessage('restore', 'home'),
                $home->getAddress(),
                $home->getCity(),
            )
        );

        return $this->redirectToRoute('home_list');
    }
}
