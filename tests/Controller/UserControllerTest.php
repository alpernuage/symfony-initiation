<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\DataProviderTrait;
use App\Tests\TranslatorTrait;
use App\Tests\WebTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    use DataProviderTrait;
    use TranslatorTrait;
    use WebTestTrait;

    /**
     * @return array<array<string>>
     */
    public function invalidEmailErrorMessageProvider(): array
    {
        return [
            ['fr', "Cette valeur n'est pas une adresse email valide."],
            ['en', 'This value is not a valid email address.'],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public function tooLongTextErrorMessageProvider(): array
    {
        return [
            ['fr', "Cette chaîne est trop longue. Elle doit avoir au maximum 255 caractères."],
            ['en', 'This value is too long. It should have 255 characters or less.'],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public function uniqueEmailErrorMessageProvider(): array
    {
        return [
            ['fr', 'La valeur "test.user@example.com" est déjà utilisée.'],
            ['en', 'The value "test.user@example.com" is already used.'],
        ];
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testListUsers(string $locale): void
    {
        static::createClient()->request(Request::METHOD_GET, sprintf('/%s/users', $locale));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $translatedText = static::getTranslator()->trans('list', locale: $locale) . ' ' . static::getTranslator()->trans('labels.users', locale: $locale);

        self::assertSelectorTextContains('h1', $translatedText);
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testCreateUser(string $locale): void
    {
        // Given the "Alper" user isn't created
        $client = static::createClient();
        $crawler = $client->request('GET', sprintf('/%s/user/create', $locale));

        self::assertRouteSame('user_create');

        $entityName = self::getTranslatedEntityName($locale, 'user');
        $translatedCancelButtonText = self::getTranslatedActionText('cancel', $entityName, $locale);
        $link = $crawler->filter("a:contains($translatedCancelButtonText)")->link();
        $client->click($link);

        $translatedText = static::getTranslator()->trans('list', locale: $locale) . ' ' . static::getTranslator()->trans('labels.users', locale: $locale);

        self::assertSelectorTextContains('h1', $translatedText);
        self::assertRouteSame('user_list');

        $notCreatedUser = $this->getUserRepository()->findOneByEmail('alper.akbulut@alximy.io');

        self::assertNull($notCreatedUser);

        $translatedSuccessMessage = self::getTranslatedSuccessMessage('create', $entityName, ['Alper', 'AKBULUT'], $locale);

        self::assertSelectorNotExists("div:contains('$translatedSuccessMessage')");

        // When the user submits creation with valid data
        $client->request('GET', sprintf('/%s/user/create', $locale));
        $this->submitCreateOrUpdateUserForm($client, $locale, 'Alper', 'AKBULUT', 'alper.akbulut@alximy.io');

        // Then the "Alper" user is successfully created
        self::assertResponseRedirects();
        $client->followRedirect();

        self::assertSelectorExists("div:contains('$translatedSuccessMessage')");
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $translatedShowText = self::getTranslatedActionText('show', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedShowText);
        self::assertSelectorTextContains('td', 'Alper');
    }

    /**
     * @dataProvider invalidEmailErrorMessageProvider
     */
    public function testCreateUserWithInvalidEmail(string $locale, string $expectedErrorMessage): void
    {
        // Given the "Alper" user isn't created
        $client = static::createClient();
        $client->request('GET', sprintf('/%s/user/create', $locale));

        // When we create the "Alper" user with INVALID email address
        $this->submitCreateOrUpdateUserForm($client, $locale, 'Alper', 'AKBULUT', 'invalid-email@example');

        // Then the "Alper" user creation fails
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $entityName = self::getTranslatedEntityName($locale, 'user');
        $translatedCreateText = self::getTranslatedActionText('create', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedCreateText);
        self::assertSelectorTextContains('div ul li', $expectedErrorMessage);
    }

    /**
     * @dataProvider tooLongTextErrorMessageProvider
     */
    public function testCreateUserWithTooLongFields(string $locale, string $expectedErrorMessage): void
    {
        // Given the "Alice" user isn't created
        $client = static::createClient();
        $client->request('GET', sprintf('/%s/user/create', $locale));

        // When we create the "Alice" user with TOO LONG firstName and lastName
        $this->submitCreateOrUpdateUserForm(
            $client,
            $locale,
            'Alice Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'valid-email@example.com'
        );

        // Then the "Alice" user creation fails
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $entityName = self::getTranslatedEntityName($locale, 'user');
        $translatedCreateText = self::getTranslatedActionText('create', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedCreateText);
        self::assertSelectorTextContains('div ul li', $expectedErrorMessage);
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testShowUser(string $locale): void
    {
        // Given the "John" user is created
        $client = static::createClient();
        $testUser = static::getTestUser();

        // When we view the "John" user
        $client->request(Request::METHOD_GET, sprintf('/%s/user/' . $testUser->getId(), $locale));

        // Then the "John" user is successfully viewed
        self::assertRouteSame('user_show', ['id' => $testUser->getId()]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $entityName = self::getTranslatedEntityName($locale, 'user');
        $translatedShowText = self::getTranslatedActionText('show', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedShowText);
        self::assertSelectorTextContains('td', $testUser->getFirstName());
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testUpdateUser(string $locale): void
    {
        // Given the "John" user is created
        $client = static::createClient();
        $testUser = static::getTestUser();
        $client->request(Request::METHOD_GET, sprintf('/%s/user/edit/' . $testUser->getId(), $locale));

        self::assertRouteSame('user_edit', ['id' => $testUser->getId()]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $entityName = self::getTranslatedEntityName($locale, 'user');
        $translatedEditText = self::getTranslatedActionText('edit', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedEditText);
        self::assertInputValueSame('user_form[email]', strval($testUser->getEmail()));

        // When we update the "John" user with the following data
        $this->submitCreateOrUpdateUserForm($client, $locale, 'Emma', 'BROWN', 'updated.email@example.com');

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

        $translatedSuccessMessage = self::getTranslatedSuccessMessage('edit', $entityName, ['Emma', 'BROWN'], $locale);

        self::assertSelectorExists("div:contains('$translatedSuccessMessage')");

        $translatedShowText = self::getTranslatedActionText('show', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedShowText);
        self::assertSelectorTextContains('td', 'Emma');
    }

    /**
     * @dataProvider invalidEmailErrorMessageProvider
     */
    public function testUpdateUserWithInvalidEmail(string $locale, string $expectedErrorMessage): void
    {
        // Given the "John" user is created
        $client = static::createClient();
        $testUser = static::getTestUser();
        $client->request(Request::METHOD_GET, sprintf('/%s/user/edit/' . $testUser->getId(), $locale));

        // When we update the "John" user with the following INVALID email address
        $this->submitCreateOrUpdateUserForm($client, $locale, 'Emma', 'BROWN', 'invalid-email@example');

        // Then the "John" user update fails
        $notUpdatedUser = $this->getUserRepository()->findOneBy(['email' => 'invalid-email@example']);
        self::assertNull($notUpdatedUser);

        $entityName = self::getTranslatedEntityName($locale, 'user');
        $translatedSuccessMessage = self::getTranslatedSuccessMessage('edit', $entityName, ['Emma', 'BROWN'], $locale);

        self::assertSelectorNotExists("div:contains('$translatedSuccessMessage')");
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $translatedEditText = self::getTranslatedActionText('edit', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedEditText);
        self::assertSelectorTextContains('div ul li', $expectedErrorMessage);
    }

    /**
     * @dataProvider tooLongTextErrorMessageProvider
     */
    public function testUpdateUserWithTooLongFields(string $locale, string $expectedErrorMessage): void
    {
        // Given the "John" user update is created
        $client = static::createClient();
        $testUser = static::getTestUser();
        $client->request(Request::METHOD_GET, '/' . $locale . '/user/edit/' . $testUser->getId());

        // When we update the "John" user with the following TOO LONG firstName and lastName
        $this->submitCreateOrUpdateUserForm(
            $client,
            $locale,
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel nisi sit amet lacus pulvinar ullamcorper. Etiam neque orci, feugiat nec augue a, dapibus eleifend ante. Nam congue, tellus in hendrerit cursus, lacus justo iaculis sapien, sit amet tempor elit erat in ex. Cras a ipsum sit amet fusce.',
            'valid-email@test.com'
        );

        // Then the "John" user update fails
        $notUpdatedUser = $this->getUserRepository()->findOneBy(['email' => 'valid-email@example.com']);
        self::assertNull($notUpdatedUser);

        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $entityName = self::getTranslatedEntityName($locale, 'user');
        $translatedEditText = self::getTranslatedActionText('edit', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedEditText);
        self::assertSelectorTextContains('div ul li', $expectedErrorMessage);
    }

    /**
     * @dataProvider uniqueEmailErrorMessageProvider
     */
    public function testUniqueEmailConstraintOnUpdate(string $locale, string $expectedErrorMessage): void
    {
        // Given the "John" user is created
        $client = static::createClient();

        // Then create the "Alper" user with NON EXISTING email
        $client->request('GET', sprintf('/%s/user/create', $locale));
        self::submitCreateOrUpdateUserForm($client, $locale, 'Alper', 'AKBULUT', 'alper.akbulut@alximy.io');
        $user = $this->getUserRepository()->findOneBy(['email' => 'alper.akbulut@alximy.io']);

        // When we update the "Alper" user with the following EXISTING email address
        /** @var User $user */
        $client->request(Request::METHOD_GET, '/' . $locale . '/user/edit/' . $user->getId());
        $this->submitCreateOrUpdateUserForm($client, $locale, 'Emma', 'BROWN', 'test.user@example.com');

        // Then the "Alper" user update fails
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSelectorExists('ul li');
        self::assertSelectorTextContains('div ul li', $expectedErrorMessage);
    }

    /**
     * @dataProvider uniqueEmailErrorMessageProvider
     */
    public function testUniqueEmailConstraintOnCreate(string $locale, string $expectedErrorMessage): void
    {
        // Given the "Alper" user with following email address isn't created
        $client = static::createClient();
        $user = $this->getUserRepository()->findOneBy(['email' => 'alper.akbulut@alximy.io']);
        self::assertNull($user);

        // When we create the "Alper" user with email address "alper.akbulut@alximy.io"
        $client->request('GET', sprintf('/%s/user/create', $locale));
        $this->submitCreateOrUpdateUserForm($client, $locale, 'Alper', 'AKBULUT', 'alper.akbulut@alximy.io');

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Then we find the created "Alper" user with "alper.akbulut@alximy.io" email address
        $user = $this->getUserRepository()->findOneBy(['email' => 'alper.akbulut@alximy.io']);
        self::assertNotNull($user);

        // When we create the "David" user with EXISTING email address "alper.akbulut@alximy.io"
        $client->request('GET', sprintf('/%s/user/create', $locale));
        $this->submitCreateOrUpdateUserForm($client, $locale, 'David', 'AKBULUT', 'alper.akbulut@alximy.io');

        // Then the "David" user creation fails
        static::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        if ($locale === 'fr') {
            $expectedErrorMessage = 'La valeur "alper.akbulut@alximy.io" est déjà utilisée.';
        }
        if ($locale === 'en') {
            $expectedErrorMessage = 'The value "alper.akbulut@alximy.io" is already used';
        }
        self::assertSelectorTextContains('div ul li', $expectedErrorMessage);
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testRemoveUser(string $locale): void
    {
        // Given the existing "John" user with the email address "test.user@example.com"
        $client = static::createClient();
        $testUser = static::getTestUser();

        // When we remove the "John" user
        $crawler = $client->request(Request::METHOD_GET, sprintf('/%s/users', $locale));
        $form = $crawler->filter('form[action="/' . $locale . '/user/delete/' . $testUser->getId() . '"]')->form();
        $client->submit($form);

        // Then the "John" user is removed
        self::assertRouteSame('user_delete', ['id' => $testUser->getId()]);
        self::assertNull($this->getUserRepository()->find($testUser->getId()));

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertRouteSame('user_list');

        $entityName = self::getTranslatedEntityName($locale, 'user');
        $translatedSuccessMessage = self::getTranslatedSuccessMessage('delete', $entityName, ['John', 'DOE'], $locale);

        self::assertSelectorExists("div:contains('$translatedSuccessMessage')");
    }

    private function submitCreateOrUpdateUserForm(KernelBrowser $client, string $locale, string $firstName, string $lastName, ?string $email = null): void
    {
        $saveButton = self::getTranslator()->trans('buttons.save', locale: $locale);

        $client->submitForm($saveButton, [
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
}
