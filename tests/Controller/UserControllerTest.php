<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class UserControllerTest extends WebTestCase
{
    public function testListUsers(): void
    {
        static::createClient()->request(Request::METHOD_GET, '/users');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', 'Users list');
    }

    public function testShowUser(): void
    {
        // Given client with User Repository
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // When the user clicks on "Show User" button of a user having this email
        $testUser = $userRepository->findOneBy(['email' => 'test.user@example.com']);
        $client->request(Request::METHOD_GET, '/user/' . $testUser->getId());

        // Then the details ot selected user should be displayed
        $this->assertRouteSame('user_show', ['id' => $testUser->getId()]);
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', 'User Details');
        $this->assertSelectorTextContains('td', $testUser->getFirstName());
    }
}
