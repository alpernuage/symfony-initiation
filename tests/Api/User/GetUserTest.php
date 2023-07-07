<?php

namespace App\Tests\Api\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetUserTest extends ApiTestCase
{
    use UserApiTestTrait;

    public function testGetUserReturnsUserSuccessfully(): void
    {
        // Given an "existing user"
        $user = $this->getRandomUser();

        self::assertInstanceOf(User::class, $user);

        // When we request the "existing user"
        self::createClient()->request(Request::METHOD_GET, '/api/users/' . $user->getId());

        // Then we receive the existing user
        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'firstName' => 'John',
            'lastName' => 'DOE',
            'email' => 'test.user@example.com',
        ]);
    }

    public function testGetNonExistentUserReturnsNotFound(): void
    {
        // Given a non-existent user id
        $nonexistentUserId = 'nonexistent-id';

        // When we get user with non-existent id
        self::createClient()->request(Request::METHOD_GET, '/api/users/' . $nonexistentUserId);

        // Then the "non-existent user" is not found
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertJsonContains([
            '@context' => '/api/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:description' => 'Invalid identifier value or configuration.',
        ]);
    }

    public function testFindUserByIriReturnsUserSuccessfully(): void
    {
        // Given "random existing user"
        $user = $this->getRandomUser();

        // When we request the "random existing user"
        /** @var string $iri */
        $iri = $this->findIriBy(User::class, ['firstName' => $user->getFirstName()]);
        self::createClient()->request(Request::METHOD_GET, $iri);

        // Then the response is successful
        self::assertResponseIsSuccessful();
    }
}
