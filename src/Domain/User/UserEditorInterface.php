<?php

namespace App\Domain\User;

use App\Entity\User;

interface UserEditorInterface
{
    public function edit(User $user, UserInput $userInput): void;
}
