<?php

namespace App\Tests\Api\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class UpdateUserTest extends ApiTestCase
{
    use UserApiTestTrait;

    public function testUpdateUser(): void
    {
        // Given "random existing user"
        $user = $this->getRandomUser();

        self::assertInstanceOf(User::class, $user);
        self::assertNotEquals('Paul', $user->getFirstName());
        self::assertNotEquals('BLANC', $user->getLastName());

        // When we update the "random existing user" with the following data
        $payload = [
            "firstName" => "Paul",
            "lastName" => "BLANC",
            "email" => "paul.blanc@example.com",
        ];

        self::createClient()->request(
            Request::METHOD_PUT,
            '/api/users/' . $user->getId(),
            ['json' => $payload]
        );

        // Then the "random existing user" is successfully updated
        self::assertResponseIsSuccessful();

        // And we find the "random existing user" with the same "{id}" and the updated data
        /** @var User $updatedUser */
        $updatedUser = $this->getUserRepository()->find($user->getId());

        self::assertSame('Paul', $updatedUser->getFirstName());
        self::assertSame('BLANC', $updatedUser->getLastName());
    }
}
