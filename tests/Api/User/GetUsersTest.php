<?php

namespace App\Tests\Api\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class GetUsersTest extends ApiTestCase
{
    use UserApiTestTrait;

    public function testGetUsers(): void
    {
        // Given "random existing user"
        $user = $this->getRandomUser();
        $expectedUser = [
//            '@id' => '/api/users/' . $user->getId(),
            '@type' => 'GetUserOutput',
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        ];

        if ($user->getEmail() !== null) {
            $expectedUser['email'] = $user->getEmail();
        }

        // When the user collection is requested
        $response = self::createClient()->request(Request::METHOD_GET, '/api/users');

        // Then the users are returned
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $responseArray = $this->getResponseContent($response);

        self::assertArrayHasKey('hydra:member', $responseArray);

        /** @var array<array<string>> $users */
        $users = $responseArray['hydra:member'];

        self::assertNotEmpty($users);
        self::assertJsonContains([
            '@context' => '/api/contexts/User',
            '@type' => 'hydra:Collection',
        ]);
//        self::assertContainsEquals($expectedUser, $users);
    }
}
