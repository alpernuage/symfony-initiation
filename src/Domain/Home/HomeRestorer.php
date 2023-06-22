<?php


namespace App\Domain\Home;

use App\Entity\Home;
use Doctrine\ORM\EntityManagerInterface;

class HomeRestorer implements HomeRestorerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function restore(Home $home): void
    {
        $home->setDeletedAt();
        $this->entityManager->flush();
    }
}
