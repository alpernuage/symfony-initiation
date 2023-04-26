<?php

namespace App\Domain\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserEditor implements UserEditorInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function edit(User $user, UserInput $userInput): void
    {
        $user->setFirstName($userInput->firstName);
        $user->setLastName($userInput->lastName);
        $user->setEmail($userInput->email);

        $this->entityManager->flush();
    }
}
