<?php

namespace App\Domain\Home;

use App\Entity\Home;

interface HomeRestorerInterface
{
    public function restore(Home $home): void;
}
