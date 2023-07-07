<?php

namespace App\Tests\Api\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\ApiTestTrait;

trait UserApiTestTrait
{
    use ApiTestTrait;

    public static function getTestUser(): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::getService(UserRepository::class);

        /** @var User */
        return $userRepository->findOneBy(['email' => 'test.user@example.com']);
    }

    private function getRandomUser(): User
    {
        /** @var User */
        return $this->getUserRepository()->findOneBy([]);
    }

    private function getUserRepository(): UserRepository
    {
        /** @var UserRepository */
        return static::getService(UserRepository::class);
    }
}
