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

    private const ENTITY_NAME = "Utilisateur";
    private const TOO_LONG_STRING_ERROR_MESSAGE = "Cette chaîne est trop longue. Elle doit avoir au maximum 255 caractères.";
    private const INVALID_EMAIL_ERROR_MESSAGE = "Cette valeur n'est pas une adresse email valide.";

    private function submitCreateOrUpdateUserForm(KernelBrowser $client, string $firstName, string $lastName, ?string $email = null): void
    {
        $client->submitForm(self::SAVE_BUTTON, [
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

        $translatedText = static::getTranslator()->trans('list') . ' ' . static::getTranslator()->trans('labels.users');

        self::assertSelectorTextContains('h1', $translatedText);
    }

    public function testCreateUser(): void
    {
        // Given the "Alper" user isn't created
        $client = static::createClient();
        $crawler = $client->request('GET', '/user/create');

        self::assertRouteSame('user_create');

        $translatedCancelButtonText = self::getTranslatedActionText('cancel', self::ENTITY_NAME);
        $link = $crawler->filter("a:contains($translatedCancelButtonText)")->link();
        $client->click($link);

        $translatedText = static::getTranslator()->trans('list') . ' ' . static::getTranslator()->trans('labels.users');

        self::assertSelectorTextContains('h1', $translatedText);
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

        $translatedSuccessMessage = self::getTranslatedSuccessMessage('create', self::ENTITY_NAME, ['Alper', 'AKBULUT']);

        self::assertSelectorExists("div:contains('$translatedSuccessMessage')");
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $translatedShowText = self::getTranslatedActionText('show', self::ENTITY_NAME);

        self::assertSelectorTextContains('h1', $translatedShowText);
        self::assertSelectorTextContains('td', 'Alper');
    }

    public function testCreateUserWithInvalidEmail(): void
    {
        // Given the "Alper" user isn't created
        $client = static::createClient();
        $client->request('GET', '/user/create');

        // When we create the "Alper" user with INVALID email address
        $this->submitCreateOrUpdateUserForm($client, 'Alper', 'AKBULUT', 'invalid-email@example');

        // Then the "Alper" user creation fails
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $translatedCreateText = self::getTranslatedActionText('create', self::ENTITY_NAME);

        self::assertSelectorTextContains('h1', $translatedCreateText);
        self::assertSelectorTextContains('ul li', self::INVALID_EMAIL_ERROR_MESSAGE);
    }

    public function testCreateUserWithTooLongFields(): void
    {
        // Given the "Alice" user isn't created
        $client = static::createClient();
        $client->request('GET', '/user/create');

        // When we create the "Alice" user with TOO LONG firstName and lastName
        $this->submitCreateOrUpdateUserForm(
            $client,
            'Alice Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'valid-email@example.com'
        );

        // Then the "Alice" user creation fails
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $translatedCreateText = self::getTranslatedActionText('create', self::ENTITY_NAME);

        self::assertSelectorTextContains('h1', $translatedCreateText);
        self::assertSelectorTextContains('ul li', self::TOO_LONG_STRING_ERROR_MESSAGE);
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

        $translatedShowText = self::getTranslatedActionText('show', self::ENTITY_NAME);

        self::assertSelectorTextContains('h1', $translatedShowText);
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

        $translatedEditText = self::getTranslatedActionText('edit', self::ENTITY_NAME);

        self::assertSelectorTextContains('h1', $translatedEditText);
        self::assertInputValueSame('user_form[email]', strval($testUser->getEmail()));

        // When we update the "John" user with the following data
        $this->submitCreateOrUpdateUserForm($client, 'Emma', 'BROWN', 'updated.email@example.com');

        // Then the "John" user is successfully updated
        /** @var User $updatedUser */
        $updatedUser = $this->getUserRepository()->find($testUser->getId());

        self::assertEquals('Emma', $updatedUser->getFirstName());
        self::assertEquals('BROWN', $updatedUser->getLastName());
        self::assertEquals('updated.email@example.com', $updatedUser->getEmail());

        // And we find the "John" user with the same "{id}" and the updated data
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertRouteSame('user_show', ['id' => $testUser->getId()]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $translatedSuccessMessage = self::getTranslatedSuccessMessage('edit', self::ENTITY_NAME, ['Emma', 'BROWN']);

        self::assertSelectorExists("div:contains('$translatedSuccessMessage')");

        $translatedShowText = self::getTranslatedActionText('show', self::ENTITY_NAME);

        self::assertSelectorTextContains('h1', $translatedShowText);
        self::assertSelectorTextContains('td', 'Emma');
    }

    public function testUpdateUserWithInvalidEmail(): void
    {
        // Given the "John" user is created
        $client = static::createClient();
        $testUser = static::getTestUser();
        $client->request(Request::METHOD_GET, '/user/edit/' . $testUser->getId());

        // When we update the "John" user with the following INVALID email address
        $this->submitCreateOrUpdateUserForm($client, 'Emma', 'BROWN', 'invalid-email@example');

        // Then the "John" user update fails
        $notUpdatedUser = $this->getUserRepository()->findOneBy(['email' => 'invalid-email@example']);
        self::assertNull($notUpdatedUser);
        self::assertSelectorNotExists('div:contains("User Emma BROWN updated with success.")');

        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $translatedEditText = self::getTranslatedActionText('edit', self::ENTITY_NAME);

        self::assertSelectorTextContains('h1', $translatedEditText);
        self::assertSelectorTextContains('ul li', self::INVALID_EMAIL_ERROR_MESSAGE);
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
        $notUpdatedUser = $this->getUserRepository()->findOneBy(['email' => 'valid-email@example.com']);
        self::assertNull($notUpdatedUser);

        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $translatedEditText = self::getTranslatedActionText('edit', self::ENTITY_NAME);

        self::assertSelectorTextContains('h1', $translatedEditText);
        self::assertSelectorTextContains('ul li', self::TOO_LONG_STRING_ERROR_MESSAGE);
    }

    public function testUniqueEmailConstraintOnUpdate(): void
    {
        // Given the "John" user is created
        $client = static::createClient();
        $testUser = static::getTestUser();
        self::assertNotNull($testUser);
        self::assertInstanceOf(User::class, $testUser);

        // Then create the "Alper" user with NON EXISTING email
        $client->request('GET', '/user/create');
        self::submitCreateOrUpdateUserForm($client, 'Alper', 'AKBULUT', 'alper.akbulut@alximy.io');
        $user = $this->getUserRepository()->findOneBy(['email' => 'alper.akbulut@alximy.io']);

        // When we update the "Alper" user with the following EXISTING email address
        $client->request(Request::METHOD_GET, '/user/edit/' . $user->getId());
        $this->submitCreateOrUpdateUserForm($client, 'Emma', 'BROWN', 'test.user@example.com');

        // Then the "Alper" user update fails
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSelectorExists('ul li');
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
        $this->submitCreateOrUpdateUserForm($client, 'David', 'AKBULUT', 'alper.akbulut@alximy.io');

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

        $translatedSuccessMessage = self::getTranslatedSuccessMessage('delete', self::ENTITY_NAME, ['John', 'DOE']);

        self::assertSelectorExists("div:contains('$translatedSuccessMessage')");
    }
}
