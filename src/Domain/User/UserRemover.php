<?php

namespace App\Domain\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserRemover implements UserRemoverInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function remove(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
