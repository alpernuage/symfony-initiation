<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;

trait WebTestTrait
{
    public static function getService(string $service): object
    {
        return static::getContainer()->get($service);
    }

    public static function getTestUser(): ?User
    {
        return self::getService(UserRepository::class)->findOneBy(['email' => 'test.user@example.com']);
    }
}
