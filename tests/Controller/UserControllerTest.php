<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    public function testListUsers(): void
    {
        static::createClient()->request(Request::METHOD_GET, '/users');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', 'Users list');
    }

    public function testCreateUser(): void
    {
        // Given the create user page is displayed
        $client = static::createClient();
        $crawler = $client->request('GET', '/user/create');

        $this->assertRouteSame('user_create');
        $this->assertSelectorTextContains('h1', 'Create User');

        // When the user clicks on "return to user list" button
        $link = $crawler->filter('a:contains("Cancel and return to the user list")')->link();
        $client->click($link);

        // Then the user list page should be displayed
        $this->assertSelectorTextContains('h1', 'Users list');
        $this->assertRouteSame('user_list');

        // And the user is not created yet
        $userRepository = static::getContainer()->get(UserRepository::class);
        $notCreatedUser = $userRepository->findOneByEmail('alper.akbulut@alximy.io');

        $this->assertNull($notCreatedUser);
        $this->assertSelectorNotExists('div:contains("New user Alper AKBULUT created with success.")');

        // When the user submits the create user form with valid data
        $client->request('GET', '/user/create');

        $client->submitForm('Create User', [
            'user_form[firstName]' => 'Alper',
            'user_form[lastName]' => 'AKBULUT',
            'user_form[email]' => 'alper.akbulut@alximy.io',
        ]);

        // Then the user should be redirected to the user details page
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorExists('div:contains("New user Alper AKBULUT created with success.")');

        // And the newly created user details should be displayed
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('h1', 'User Details');
        $this->assertSelectorTextContains('td', 'Alper');
    }

    public function testCreateUserWithInvalidEmail(): void
    {
        // Given the create user page is displayed
        $client = static::createClient();
        $client->request('GET', '/user/create');

        // When the user submits the create user form with INVALID data
        $client->submitForm('Create User', [
            'user_form[firstName]' => 'Alper',
            'user_form[lastName]' => 'AKBULUT',
            'user_form[email]' => 'invalid-email@test',
        ]);

        // Then an error message should be shown in the same page
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertSelectorTextContains('h1', 'Create User');
        $this->assertSelectorTextContains('ul li', 'This value is not a valid email address');
    }

    public function testCreateUserWithTooLongFields(): void
    {
        // Given the create user page is displayed
        $client = static::createClient();
        $client->request('GET', '/user/create');

        // When the user submits the create user form with TOO LONG data
        $client->submitForm('Create User', [
            'user_form[firstName]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'user_form[lastName]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'user_form[email]' => 'valid-email@test.com',
        ]);

        // Then an error message should be shown in the same page
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSelectorTextContains('h1', 'Create User');
        $this->assertSelectorTextContains('ul li', 'This value is too long. It should have 255 characters or less.');

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
