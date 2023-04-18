<?php

namespace App\Domain\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserCreator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(UserInput $userInput): User
    {
        $user = $this->createUserFromDTO($userInput);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createUserFromDTO(UserInput $userInput): User
    {
        return new User(
            $userInput->firstName,
            $userInput->lastName,
            $userInput->email,
        );
    }
}
