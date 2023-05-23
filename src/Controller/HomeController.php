<?php

namespace App\Controller;

use App\Domain\PaginationTrait;
use App\Entity\Home;
use App\Domain\Home\HomeCreatorInterface;
use App\Domain\Home\HomeInput;
use App\Form\HomeFormType;
use App\Repository\HomeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

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

    #[Route('/home/{id}', name: 'home_show', requirements: ['id' => Requirement::UUID_V7], methods: [Request::METHOD_GET])]
    public function show(Home $home): Response
    {
        return $this->render('home/show.html.twig', [
            'home' => $home,
        ]);
    }

    #[Route('/home/create', name: 'home_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function create(Request $request, HomeCreatorInterface $homeCreator): Response
    {
        $homeInput = new HomeInput();

        $form = $this->createForm(HomeFormType::class, $homeInput);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $home = $homeCreator->create($homeInput);

            $message = sprintf(
                'New home %s %s created with success.',
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
}
