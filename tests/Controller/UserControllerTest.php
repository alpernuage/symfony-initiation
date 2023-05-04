<?php

namespace App\Tests\Controller;

use App\Entity\User;
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

        $client->submitForm('Save', [
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
        $client->submitForm('Save', [
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
        $client->submitForm('Save', [
            'user_form[firstName]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'user_form[lastName]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'user_form[email]' => 'valid-email@test.com',
        ]);

        // Then an error message should be shown in the same page
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSelectorTextContains('h1', 'Create User');
        $this->assertSelectorTextContains('ul li', 'This value is too long. It should have 255 characters or less.');
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

    public function testUpdateUser(): void
    {
        // Given client with User Repository
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // When the user clicks on "Edit User" button of a user having this email
        $testUser = $userRepository->findOneBy(['email' => 'test.user@example.com']);
        $client->request(Request::METHOD_GET, '/user/edit/' . $testUser->getId());

        // Then the details ot selected user should be displayed in editable inputs
        $this->assertRouteSame('user_edit', ['id' => $testUser->getId()]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Edit User');
        $this->assertInputValueSame('user_form[email]', $testUser->getEmail());

        // When the user submits the edit user form
        $client->submitForm('Save', [
            'user_form[firstName]' => 'Updated First Name',
            'user_form[lastName]' => 'Updated Last Name',
            'user_form[email]' => 'updated.email@example.com',
        ]);

        // Then the user should be updated in the database
        $updatedUser = $userRepository->find($testUser->getId());
        $this->assertEquals('Updated First Name', $updatedUser->getFirstName());
        $this->assertEquals('Updated Last Name', $updatedUser->getLastName());
        $this->assertEquals('updated.email@example.com', $updatedUser->getEmail());

        // Then the user should be redirected to the user details page
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertRouteSame('user_show', ['id' => $testUser->getId()]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('div:contains("User Updated First Name Updated Last Name updated with success.")');

        // And html tags could be found with correct contents
        $this->assertSelectorTextContains('h1', 'User Details');
        $this->assertSelectorTextContains('td', 'Updated First Name');
    }

    public function testUpdateUserWithInvalidEmail(): void
    {
        // Given client with User Repository
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // When the user clicks on "Edit User" button of a user having this email
        $testUser = $userRepository->findOneBy(['email' => 'test.user@example.com']);
        $client->request(Request::METHOD_GET, '/user/edit/' . $testUser->getId());

        // When the user submits the edit user form with INVALID data
        $client->submitForm('Save', [
            'user_form[firstName]' => 'Updated First Name',
            'user_form[lastName]' => 'Updated Last Name',
            'user_form[email]' => 'invalid-email@test',
        ]);

        // Then the user should not be updated in the database
        $notUpdatedUser = $userRepository->findOneBy(['email' => 'invalid-email@test']);
        $this->assertNull($notUpdatedUser);
        $this->assertSelectorNotExists('div:contains("User Updated First Name Updated Last Name updated with success.")');

        // Then an error message should be shown in the same page
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertSelectorTextContains('h1', 'Edit User');
        $this->assertSelectorTextContains('ul li', 'This value is not a valid email address');
    }

    public function testUpdateUserWithTooLongFields(): void
    {
        // Given client with User Repository
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // When the user clicks on "Edit User" button of a user having this email
        $testUser = $userRepository->findOneBy(['email' => 'test.user@example.com']);
        $client->request(Request::METHOD_GET, '/user/edit/' . $testUser->getId());

        // When the user submits the edit user form with TOO LONG data
        $client->submitForm('Save', [
            'user_form[firstName]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'user_form[lastName]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'user_form[email]' => 'valid-email@test.com',
        ]);

        // Then the user should not be updated in the database
        $notUpdatedUser = $userRepository->findOneBy(['email' => 'valid-email@test.comt']);
        $this->assertNull($notUpdatedUser);

        // Then an error message should be shown in the same page
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertSelectorTextContains('h1', 'Edit User');
        $this->assertSelectorTextContains('ul li', 'This value is too long. It should have 255 characters or less.');
    }

    public function testUniqueEmailConstraintOnUpdate(): void
    {
        // Given user repository
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Then find a user existing in database
        $user = $userRepository->findOneBy(['email' => 'test.user@example.com']);
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);

        // Then a user is updated with an EXISTING email
        $client->request(Request::METHOD_GET, '/user/edit/' . $user->getId());
        $client->submitForm('Save', [
            'user_form[firstName]' => 'Updated First Name',
            'user_form[lastName]' => 'Updated Last Name',
            'user_form[email]' => 'test.user@example.com',
        ]);

        // Then the UPDATE FAILS and an error message displays
        $this->assertSelectorTextContains('ul li', 'The value "test.user@example.com" is already used');
    }
    
    public function testUniqueEmailConstraintOnCreate(): void
    {
        // Given user repository
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // Then verify that user doesn't exist in database
        $user = $userRepository->findOneBy(['email' => 'alper.akbulut@alximy.io']);
        $this->assertNull($user);

        // When the user creates a user
        $client->request('GET', '/user/create');
        $client->submitForm('Save', [
            'user_form[firstName]' => 'Alper',
            'user_form[lastName]' => 'AKBULUT',
            'user_form[email]' => 'alper.akbulut@alximy.io',
        ]);
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Then find created user in database
        $user = $userRepository->findOneBy(['email' => 'alper.akbulut@alximy.io']);
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);

        // When the user creates a user with EXISTING email
        $client->request('GET', '/user/create');
        $client->submitForm('Save', [
            'user_form[firstName]' => 'Alper',
            'user_form[lastName]' => 'AKBULUT',
            'user_form[email]' => 'alper.akbulut@alximy.io',
        ]);

        // Then the CREATION FAILS and an error message displays
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSelectorTextContains('ul li', 'The value "alper.akbulut@alximy.io" is already used');
    }
}
