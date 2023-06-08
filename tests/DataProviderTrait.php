<?php

namespace App\Tests;

trait DataProviderTrait
{
    /**
     * @return array<array<string>>
     */
    public function languagesProvider(): array
    {
        return [
            ['fr'],
            ['en'],
        ];
    }
}
