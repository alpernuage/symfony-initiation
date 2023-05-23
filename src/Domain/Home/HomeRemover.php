<?php

namespace App\Domain\Home;

use App\Entity\Home;
use Doctrine\ORM\EntityManagerInterface;

class HomeRemover implements HomeRemoverInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function remove(Home $home): void
    {
        $this->entityManager->remove($home);
        $this->entityManager->flush();
    }
}
