<?php

namespace App\Domain\Home;

use App\Entity\Home;

interface HomeRemoverInterface
{
    public function remove(Home $home): void;
    public function hardRemove(Home $home): void;
}
