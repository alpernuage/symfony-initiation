<?php

namespace App\Tests\Api\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteUserTest extends ApiTestCase
{
    use UserApiTestTrait;

    public function testDeleteUser(): void
    {
        // Given "random existing user"
        $user = $this->getRandomUser();

        self::assertInstanceOf(User::class, $user);

        // When we delete the "random existing user"
        self::createClient()->request(Request::METHOD_DELETE, '/api/users/' . $user->getId());

        // Then the "random existing user" is successfully deleted
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
