<?php

namespace App\Domain\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserRestorer implements UserRestorerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function restore(User $user): void
    {
        $user->setDeletedAt();
        $this->entityManager->flush();
    }
}
