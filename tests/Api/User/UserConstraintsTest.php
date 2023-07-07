<?php

namespace App\Tests\Api\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserConstraintsTest extends ApiTestCase
{
    use UserApiTestTrait;

    public function testCreateUserValidationWithEmptyLastName(): void
    {
        // Given user values without lastName to create a user
        $payload = [
            "firstName" => "Paul",
            "email" => "paul.blanc@example.com",
        ];

        // When we create the "Paul BLANC" user without specifying the lastName
        self::createClient()->request(
            Request::METHOD_POST,
            '/api/users',
            ['json' => $payload]
        );

        // Then the "Paul BLANC" user creation fails
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJsonContains([
            '@context' => '/api/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:description' => 'Cannot create an instance of "App\Entity\User" from serialized data because its constructor requires parameter "lastName" to be present.',
        ]);
    }

    public function testCreateUserValidationForInvalidEmail(): void
    {
        // Given "Jack" user with an invalid email format
        $payload = [
            "firstName" => "Jack",
            "lastName" => "POT",
            "email" => "invalid.email",
        ];

        // When we create the "John DOE" user with an invalid email
        self::createClient()->request(Request::METHOD_POST, '/api/users', ['json' => $payload]);

        // Then the user creation fails due to validation error
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:description' => "email: Cette valeur n'est pas une adresse email valide.",
        ]);
    }

    public function testUpdateUserValidationForExistingEmail(): void
    {
        // Given the "John" user is created
        $testUser = self::getTestUser();

        // Then create the "Paul BLANC" user with NON EXISTING email
        $payload = [
            "firstName" => "Paul",
            "lastName" => "BLANC",
            "email" => "paul.blanc@example.com",
        ];

        self::createClient()->request(
            Request::METHOD_POST,
            '/api/users',
            ['json' => $payload]
        );

        // Then the "Paul BLANC" user is successfully created
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        /** @var User $user */
        $user = $this->getUserRepository()->findOneBy(["email" => "paul.blanc@example.com"]);

        // When we update the "Paul user" with the following EXISTING email address
        $payload = [
            "firstName" => "Paul",
            "lastName" => "BLANC",
            "email" => $testUser->getEmail(),
        ];

        self::createClient()->request(
            Request::METHOD_PUT,
            '/api/users/' . $user->getId(),
            ['json' => $payload]
        );

        // Then the "Paul" user update fails
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:description' => 'email: Cette valeur est déjà utilisée.',
        ]);
    }
}
