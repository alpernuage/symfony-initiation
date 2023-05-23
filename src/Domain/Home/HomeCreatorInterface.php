<?php

namespace App\Domain\Home;

use App\Entity\Home;

interface HomeCreatorInterface
{
    public function create(HomeInput $homeInput): Home;
}
