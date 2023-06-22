<?php

namespace App\Domain\User;

use App\Entity\User;

interface UserRemoverInterface
{
    public function remove(User $user): void;
    public function hardRemove(User $user): void;
}
