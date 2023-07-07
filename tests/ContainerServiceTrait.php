<?php

namespace App\Tests;

trait ContainerServiceTrait
{
    public static function getService(string $service): object
    {
        return static::getContainer()->get($service) ?? throw new \LogicException(sprintf('Service %s not found.', $service));
    }
}
