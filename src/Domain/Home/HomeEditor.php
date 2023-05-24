<?php

namespace App\Domain\Home;

use App\Entity\Home;
use Doctrine\ORM\EntityManagerInterface;

class HomeEditor implements HomeEditorInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function edit(Home $home, HomeInput $homeInput): void
    {
        $home->setAddress($homeInput->address);
        $home->setCity($homeInput->city);
        $home->setZipCode($homeInput->zipCode);
        $home->setCountry($homeInput->country);
        $home->setCurrentlyOccupied($homeInput->currentlyOccupied);
        $home->setUser($homeInput->user);

        $this->entityManager->flush();
    }
}
