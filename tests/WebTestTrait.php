<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;

trait WebTestTrait
{
    public static function getService(string $service): object
    {
        return static::getContainer()->get($service) ?? throw new \LogicException(sprintf('Service %s not found.', $service));
    }

    public static function getTestUser(): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getService(UserRepository::class);

        /** @var User */
        return $userRepository->findOneBy(['email' => 'test.user@example.com']);
    }
}
