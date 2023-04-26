<?php

namespace App\Domain\User;

use App\Entity\User;

interface UserCreatorInterface
{
    public function create(UserInput $userInput): User;
}
