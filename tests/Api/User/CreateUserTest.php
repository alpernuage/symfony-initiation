<?php

namespace App\Tests\Api\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CreateUserTest extends ApiTestCase
{
    use UserApiTestTrait;

    public function testCreateUser(): void
    {
        // Given the "Paul BLANC" user isn't created
        $notCreatedUser = $this->getUserRepository()->findOneBy([
            "firstName" => "Paul",
            "lastName" => "BLANC",
            "email" => "paul.blanc@example.com",
        ]);

        self::assertNull($notCreatedUser);

        // When we create the "Paul BLANC" user
        $payload = [
            "firstName" => "Paul",
            "lastName" => "BLANC",
            "email" => "paul.blanc@example.com",
        ];

        $response = self::createClient()->request(
            Request::METHOD_POST,
            '/api/users',
            ['json' => $payload]
        );

        // Then the "Paul BLANC" user is successfully created
        $this->thenUserIsSuccesfullyCreated($response, "Paul", "BLANC");
    }

    private function thenUserIsSuccesfullyCreated(ResponseInterface $response, string $firstName, string $lastName): void
    {
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseArray = $this->getResponseContent($response);
        self::assertArrayHasKey('@id', $responseArray);

        $userId = $this->extractUserIdFromIri($responseArray);
        $userRepository = $this->getUserRepository();
        $createdUser = $userRepository->find($userId);

        self::assertInstanceOf(User::class, $createdUser);
        self::assertEquals($firstName, $createdUser->getFirstName());
        self::assertEquals($lastName, $createdUser->getLastName());
    }

    /**
     * @param array<string, mixed> $responseArray
     */
    private function extractUserIdFromIri(array $responseArray): string
    {
        /** @var string $stringToExplode */
        $stringToExplode = $responseArray['@id'];

        return explode("/api/users/", $stringToExplode)[1];
    }
}
