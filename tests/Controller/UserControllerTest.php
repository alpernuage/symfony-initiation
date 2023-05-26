<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\WebTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    use WebTestTrait;

    private function submitCreateOrUpdateUserForm(KernelBrowser $client, string $firstName, string $lastName, ?string $email = null): void
    {
        $client->submitForm("Save", [
            'user_form[firstName]' => $firstName,
            'user_form[lastName]' => $lastName,
            'user_form[email]' => $email,
        ]);
    }

    private function getUserRepository(): UserRepository
    {
        /** @var UserRepository */
        return static::getService(UserRepository::class);
    }

    public function testListUsers(): void
    {
        static::createClient()->request(Request::METHOD_GET, '/users');

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Users list');
    }

    public function testCreateUser(): void
    {
        // Given the "Alper" user isn't created
        $client = static::createClient();
        $crawler = $client->request('GET', '/user/create');

        self::assertRouteSame('user_create');
        self::assertSelectorTextContains('h1', 'Create User');

        $link = $crawler->filter('a:contains("Cancel and return to the user list")')->link();
        $client->click($link);

        self::assertSelectorTextContains('h1', 'Users list');
        self::assertRouteSame('user_list');

        $notCreatedUser = $this->getUserRepository()->findOneByEmail('alper.akbulut@alximy.io');

        self::assertNull($notCreatedUser);
        self::assertSelectorNotExists('div:contains("New user Alper AKBULUT created with success.")');

        // When the user submits creation with valid data
        $client->request('GET', '/user/create');
        $this->submitCreateOrUpdateUserForm($client, 'Alper', 'AKBULUT', 'alper.akbulut@alximy.io');

        // Then the "Alper" user is successfully created
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertSelectorExists('div:contains("New user Alper AKBULUT created with success.")');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'User Details');
        self::assertSelectorTextContains('td', 'Alper');
    }

    public function testCreateUserWithInvalidEmail(): void
    {
        // Given the "Alper" user isn't created
        $client = static::createClient();
        $client->request('GET', '/user/create');

        // When we create the "Alper" user with INVALID email address
        $this->submitCreateOrUpdateUserForm($client, 'Alper', 'AKBULUT', 'invalid-email@test');

        // Then the "Alper" user creation fails
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertSelectorTextContains('h1', 'Create User');
        self::assertSelectorTextContains('ul li', 'This value is not a valid email address');
    }

    public function testCreateUserWithTooLongFields(): void
    {
        // Given the "Alice" user isn't created
        $client = static::createClient();
        $client->request('GET', '/user/create');

        // When we create the "Alice" user with TOO LONG firstName and lastName
        $this->submitCreateOrUpdateUserForm(
            $client,
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'valid-email@test.com'
        );

        // Then the "Alice" user creation fails
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSelectorTextContains('h1', 'Create User');
        self::assertSelectorTextContains('ul li', 'This value is too long. It should have 255 characters or less.');
    }

    public function testShowUser(): void
    {
        // Given the "John" user is created
        $client = static::createClient();
        $testUser = static::getTestUser();

        // When we view the "John" user
        $client->request(Request::METHOD_GET, '/user/' . $testUser->getId());

        // Then the "John" user is successfully viewed
        self::assertRouteSame('user_show', ['id' => $testUser->getId()]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'User Details');
        self::assertSelectorTextContains('td', $testUser->getFirstName());
    }

    public function testUpdateUser(): void
    {
        // Given the "John" user is created
        $client = static::createClient();

        $testUser = static::getTestUser();
        $client->request(Request::METHOD_GET, '/user/edit/' . $testUser->getId());

        self::assertRouteSame('user_edit', ['id' => $testUser->getId()]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Edit User');
        self::assertInputValueSame('user_form[email]', strval($testUser->getEmail()));

        // When we update the "John" user with the following data
        $this->submitCreateOrUpdateUserForm($client, 'Updated First Name', 'Updated Last Name', 'updated.email@example.com');

        // Then the "John" user is successfully updated
        /** @var User $updatedUser */
        $updatedUser = $this->getUserRepository()->find($testUser->getId());

        self::assertEquals('Updated First Name', $updatedUser->getFirstName());
        self::assertEquals('Updated Last Name', $updatedUser->getLastName());
        self::assertEquals('updated.email@example.com', $updatedUser->getEmail());

        // And we find the "John" user with the same "{id}" and the updated data
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertRouteSame('user_show', ['id' => $testUser->getId()]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorExists('div:contains("User Updated First Name Updated Last Name updated with success.")');
        self::assertSelectorTextContains('h1', 'User Details');
        self::assertSelectorTextContains('td', 'Updated First Name');
    }

    public function testUpdateUserWithInvalidEmail(): void
    {
        // Given the "John" user is created
        $client = static::createClient();
        $testUser = static::getTestUser();
        $client->request(Request::METHOD_GET, '/user/edit/' . $testUser->getId());

        // When we update the "John" user with the following INVALID email address
        $this->submitCreateOrUpdateUserForm($client, 'Updated First Name', 'Updated Last Name', 'invalid-email@test');

        // Then the "John" user update fails
        $notUpdatedUser = $this->getUserRepository()->findOneBy(['email' => 'invalid-email@test']);
        self::assertNull($notUpdatedUser);
        self::assertSelectorNotExists('div:contains("User Updated First Name Updated Last Name updated with success.")');

        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertSelectorTextContains('h1', 'Edit User');
        self::assertSelectorTextContains('ul li', 'This value is not a valid email address');
    }

    public function testUpdateUserWithTooLongFields(): void
    {
        // Given the "John" user update is created
        $client = static::createClient();
        $testUser = static::getTestUser();
        $client->request(Request::METHOD_GET, '/user/edit/' . $testUser->getId());

        // When we update the "John" user with the following TOO LONG firstName and lastName
        $this->submitCreateOrUpdateUserForm(
            $client,
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'valid-email@test.com'
        );

        // Then the "John" user update fails
        $notUpdatedUser = $this->getUserRepository()->findOneBy(['email' => 'valid-email@test.comt']);
        self::assertNull($notUpdatedUser);

        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertSelectorTextContains('h1', 'Edit User');
        self::assertSelectorTextContains('ul li', 'This value is too long. It should have 255 characters or less.');
    }

    public function testUniqueEmailConstraintOnUpdate(): void
    {
        // Given the "John" user is created
        $client = static::createClient();
        $user = static::getTestUser();
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);

        // When we update the "John" user with the following EXISTING email address
        $client->request(Request::METHOD_GET, '/user/edit/' . $user->getId());
        $this->submitCreateOrUpdateUserForm($client, 'Updated First Name', 'Updated Last Name', 'test.user@example.com');

        // Then the "John" user update fails
        self::assertSelectorTextContains('ul li', 'The value "test.user@example.com" is already used');
    }

    public function testUniqueEmailConstraintOnCreate(): void
    {
        // Given the "Alper" user with following email address isn't created
        $client = static::createClient();
        $user = $this->getUserRepository()->findOneBy(['email' => 'alper.akbulut@alximy.io']);
        self::assertNull($user);

        // When we create the "Alper" user with email address "alper.akbulut@alximy.io"
        $client->request('GET', '/user/create');
        $this->submitCreateOrUpdateUserForm($client, 'Alper', 'AKBULUT', 'alper.akbulut@alximy.io');

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Then we find the created "Alper" user with "alper.akbulut@alximy.io" email address
        $user = $this->getUserRepository()->findOneBy(['email' => 'alper.akbulut@alximy.io']);
        $this->assertNotNull($user);
        self::assertInstanceOf(User::class, $user);

        // When we create the "David" user with EXISTING email address "alper.akbulut@alximy.io"
        $client->request('GET', '/user/create');
        $this->submitCreateOrUpdateUserForm($client, 'Alper', 'AKBULUT', 'alper.akbulut@alximy.io');

        // Then the "David" user creation fails
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSelectorTextContains('ul li', 'The value "alper.akbulut@alximy.io" is already used');
    }

    public function testRemoveUser(): void
    {
        // Given the existing "John" user with the email address "test.user@example.com"
        $client = static::createClient();
        $testUser = static::getTestUser();

        // When we remove the "John" user
        $crawler = $client->request(Request::METHOD_GET, '/users');
        $form = $crawler->filter('form[action="/user/delete/' . $testUser->getId() . '"]')->form();
        $client->submit($form);

        // Then the "John" user is removed
        self::assertRouteSame('user_delete', ['id' => $testUser->getId()]);
        self::assertNull($this->getUserRepository()->find($testUser->getId()));

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertRouteSame('user_list');
        self::assertSelectorExists('div:contains("User TestUser TEST deleted with success.")');
    }
}
