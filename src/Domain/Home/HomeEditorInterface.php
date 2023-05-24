<?php

namespace App\Domain\Home;

use App\Entity\Home;

interface HomeEditorInterface
{
    public function edit(Home $home, HomeInput $homeInput): void;
}
