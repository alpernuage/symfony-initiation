<?php

namespace App\Tests\Api\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class PartialUpdateUserTest extends ApiTestCase
{
    use UserApiTestTrait;

    public function testPartialUpdateUser(): void
    {
        // Given "random existing user"
        $user = $this->getRandomUser();

        // When we update the "random existing user" with the following data
        $payload = [
            "firstName" => "Leonardo",
        ];

        self::createClient()->request(Request::METHOD_PATCH, '/api/users/' . $user->getId(), [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json' => $payload,
        ]);

        // Then the "random existing user" is successfully updated
        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'firstName' => 'Leonardo',
            'lastName' => 'DOE',
            'email' => 'test.user@example.com',
        ]);
    }
}
