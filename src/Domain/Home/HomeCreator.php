<?php

namespace App\Domain\Home;

use App\Entity\Home;
use Doctrine\ORM\EntityManagerInterface;

class HomeCreator implements HomeCreatorInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function create(HomeInput $homeInput): Home
    {
        $home = $this->createHomeFromDTO($homeInput);

        $this->entityManager->persist($home);
        $this->entityManager->flush();

        return $home;
    }

    private function createHomeFromDTO(HomeInput $homeInput): Home
    {
        return new Home(
            $homeInput->address,
            $homeInput->city,
            $homeInput->zipCode,
            $homeInput->country,
            $homeInput->currentlyOccupied,
            $homeInput->user
        );
    }
}
