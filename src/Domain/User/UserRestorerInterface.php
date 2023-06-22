<?php

namespace App\Domain\User;

use App\Entity\User;

interface UserRestorerInterface
{
    public function restore(User $user): void;
}
