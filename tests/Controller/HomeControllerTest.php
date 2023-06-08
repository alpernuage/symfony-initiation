<?php

namespace App\Tests\Controller;

use App\Entity\Home;
use App\Repository\HomeRepository;
use App\Tests\DataProviderTrait;
use App\Tests\TranslatorTrait;
use App\Tests\WebTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends WebTestCase
{
    use DataProviderTrait;
    use TranslatorTrait;
    use WebTestTrait;

    /**
     * @dataProvider languagesProvider
     */
    public function testListHomes(string $locale): void
    {
        static::createClient()->request(Request::METHOD_GET, sprintf('/%s/homes', $locale));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $translatedText = static::getTranslator()->trans('list', locale: $locale) . ' ' . static::getTranslator()->trans('labels.homes', locale: $locale);

        self::assertSelectorTextContains('h1', $translatedText);
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testCancelButtonRedirectsToHomeListPageInCreateHomePage(string $locale): void
    {
        // Given the "75, rue Auguste Hamel Lacroix-Sur-Mer" home isn't created
        $client = static::createClient();
        $client->request('GET', sprintf('/%s/home/create', $locale));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame('home_create');

        $entityName = self::getTranslatedEntityName($locale, 'home');
        $translatedCreateText = self::getTranslatedActionText('create', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedCreateText);

        $translatedCancelButtonText = self::getTranslatedActionText('cancel', $entityName, $locale);

        $client->clickLink($translatedCancelButtonText);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $translatedText = static::getTranslator()->trans('list', locale: $locale) . ' ' . static::getTranslator()->trans('labels.homes', locale: $locale);

        self::assertSelectorTextContains('h1', $translatedText);
        self::assertRouteSame('home_list');
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testCreateHome(string $locale): void
    {
        // Given the "75, rue Auguste Hamel Lacroix-Sur-Mer" home isn't created
        $client = static::createClient();
        $client->request('GET', sprintf('/%s/home/create', $locale));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame('home_create');

        $entityName = self::getTranslatedEntityName($locale, 'home');
        $translatedCreateText = self::getTranslatedActionText('create', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedCreateText);

        // When we create the "75, rue Auguste Hamel Lacroix-Sur-Mer" home
        $home = new Home(
            '75, rue Auguste Hamel',
            'Lacroix-Sur-Mer',
            '61 868',
            'LV',
            true,
            static::getTestUser(),
        );

        $saveButton = self::getTranslator()->trans('buttons.save', locale: $locale);

        $client->submitForm($saveButton, [
            'home_form[address]' => $home->getAddress(),
            'home_form[city]' => $home->getCity(),
            'home_form[zipCode]' => $home->getZipCode(),
            'home_form[country]' => $home->getCountry(),
            'home_form[currentlyOccupied]' => $home->isCurrentlyOccupied(),
            'home_form[user]' => $home->getUser()->getId()
        ]);

        // Then the "75, rue Auguste Hamel Lacroix-Sur-Mer" home is successfully created
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects();
        $client->followRedirect();

        $translatedSuccessMessage = self::getTranslatedSuccessMessage('create', $entityName, ['75, rue Auguste Hamel', 'Lacroix-Sur-Mer'], $locale);

        self::assertSelectorExists("div:contains('$translatedSuccessMessage')");

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $entityName = self::getTranslatedEntityName($locale, 'home');
        $translatedShowText = self::getTranslatedActionText('show', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedShowText);
        self::assertSelectorTextContains('td', '75, rue Auguste Hamel');
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testShowHome(string $locale): void
    {
        // Given "random existing home"
        $client = static::createClient();
        $home = $this->getHomeRepository()->findOneBy([]);

        self::assertInstanceOf(Home::class, $home);

        // When we view the address of the "random existing home"
        $client->request(Request::METHOD_GET, sprintf('/%s/home/' . $home->getId(), $locale));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Then the "random existing home" is successfully viewed
        self::assertRouteSame('home_show', ['id' => $home->getId()]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $entityName = self::getTranslatedEntityName($locale, 'home');
        $translatedShowText = self::getTranslatedActionText('show', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedShowText);
        self::assertSelectorTextContains('td', $home->getAddress());
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testUpdateHome(string $locale): void
    {
        // Given "random existing home"
        $client = static::createClient();
        $home = $this->getHomeRepository()->findOneBy([]);

        self::assertInstanceOf(Home::class, $home);

        $client->request(Request::METHOD_GET, sprintf('/%s/home/edit/' . $home->getId(), $locale));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame('home_edit', ['id' => $home->getId()]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $entityName = self::getTranslatedEntityName($locale, 'home');
        $translatedEditText = self::getTranslatedActionText('edit', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedEditText);
        self::assertInputValueSame('home_form[city]', $home->getCity());


        // When we update the "random existing home" with the following data
        $saveButton = self::getTranslator()->trans('buttons.save', locale: $locale);
        $client->submitForm($saveButton, [
            'home_form[address]' => '75, rue Auguste Hamel',
            'home_form[city]' => 'Lacroix-Sur-Mer',
            'home_form[zipCode]' => $home->getZipCode(),
            'home_form[country]' => $home->getCountry(),
            'home_form[currentlyOccupied]' => $home->isCurrentlyOccupied(),
            'home_form[user]' => $home->getUser()->getId()
        ]);

        // Then the "random existing home" is successfully updated
        /** @var Home $updatedHome */
        $updatedHome = $this->getHomeRepository()->find($home->getId());

        self::assertEquals('75, rue Auguste Hamel', $updatedHome->getAddress());
        self::assertEquals('Lacroix-Sur-Mer', $updatedHome->getCity());

        // And we find the "random existing home" with the same "{id}" and the updated data
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame('home_show', ['id' => $home->getId()]);

        $translatedSuccessMessage = self::getTranslatedSuccessMessage('edit', $entityName, ['75, rue Auguste Hamel', 'Lacroix-Sur-Mer'], $locale);

        self::assertSelectorExists("div:contains('$translatedSuccessMessage')");

        $translatedShowText = self::getTranslatedActionText('show', $entityName, $locale);

        self::assertSelectorTextContains('h1', $translatedShowText);
        self::assertSelectorTextContains('td', '75, rue Auguste Hamel');
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testRemoveHome(string $locale): void
    {
        // Given "random existing home"
        $client = static::createClient();
        $home = $this->getHomeRepository()->findOneBy([]);

        self::assertInstanceOf(Home::class, $home);

        // When we remove the "random existing home"
        $client->request(Request::METHOD_GET, sprintf('/%s/homes', $locale));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $entityName = self::getTranslatedEntityName($locale, 'home');
        $translatedRemoveButtonText = self::getTranslatedActionText('remove', $entityName, $locale);

        $client->submitForm($translatedRemoveButtonText);

        // Then the "random existing home" is removed
        self::assertRouteSame('home_delete', ['id' => $home->getId()]);
        self::assertNull($this->getHomeRepository()->find($home->getId()));
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame('home_list');

        $translatedSuccessMessage = self::getTranslatedSuccessMessage('delete', $entityName, [$home->getAddress(), $home->getCity()], $locale);

        self::assertSelectorTextContains(
            'div.flash-notice',
            $translatedSuccessMessage
        );
    }

    private function getHomeRepository(): HomeRepository
    {
        /** @var HomeRepository */
        return static::getService(HomeRepository::class);
    }
}
