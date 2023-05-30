<?php

namespace App\Tests\Controller;

use App\Entity\Home;
use App\Repository\HomeRepository;
use App\Tests\WebTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends WebTestCase
{
    use WebTestTrait;

    public function testListHomes(): void
    {
        static::createClient()->request(Request::METHOD_GET, '/homes');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Homes list');
    }

    public function testCancelButtonRedirectsToHomeListPageInCreateHomePage(): void
    {
        // Given the "75, rue Auguste Hamel Lacroix-Sur-Mer" home isn't created
        $client = static::createClient();
        $client->request('GET', '/home/create');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame('home_create');
        self::assertSelectorTextContains('h1', 'Create Home');

        $client->clickLink("Cancel and return to the home list");

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Homes list');
        self::assertRouteSame('home_list');
    }

    public function testCreateHome(): void
    {
        // Given the "75, rue Auguste Hamel Lacroix-Sur-Mer" home isn't created
        $client = static::createClient();
        $client->request('GET', '/home/create');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame('home_create');
        self::assertSelectorTextContains('h1', 'Create Home');

        // When we create the "75, rue Auguste Hamel Lacroix-Sur-Mer" home
        $home = new Home(
            '75, rue Auguste Hamel',
            'Lacroix-Sur-Mer',
            '61 868',
            'LV',
            true,
            static::getTestUser(),
        );

        $client->submitForm("Save", [
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
        self::assertSelectorExists('div:contains("New home 75, rue Auguste Hamel Lacroix-Sur-Mer created with success.")');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Home Details');
        self::assertSelectorTextContains('td', '75, rue Auguste Hamel');
    }

    public function testShowHome(): void
    {
        // Given "random existing home"
        $client = static::createClient();
        $home = $this->getHomeRepository()->findOneBy([]);

        self::assertInstanceOf(Home::class, $home);

        // When we view the address of the "random existing home"
        $client->request(Request::METHOD_GET, '/home/' . $home->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Then the "random existing home" is successfully viewed
        self::assertRouteSame('home_show', ['id' => $home->getId()]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Home Details');
        self::assertSelectorTextContains('td', $home->getAddress());
    }

    public function testUpdateHome(): void
    {
        // Given "random existing home"
        $client = static::createClient();
        $home = $this->getHomeRepository()->findOneBy([]);

        self::assertInstanceOf(Home::class, $home);

        $client->request(Request::METHOD_GET, '/home/edit/' . $home->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame('home_edit', ['id' => $home->getId()]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Edit Home');
        self::assertInputValueSame('home_form[city]', $home->getCity());

        // When we update the "random existing home" with the following data
        $client->submitForm("Save", [
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
        self::assertSelectorExists('div:contains("Home 75, rue Auguste Hamel Lacroix-Sur-Mer updated with success.")');
        self::assertSelectorTextContains('h1', 'Home Details');
        self::assertSelectorTextContains('td', '75, rue Auguste Hamel');
    }

    public function testRemoveHome(): void
    {
        // Given "random existing home"
        $client = static::createClient();
        $home = $this->getHomeRepository()->findOneBy([]);

        self::assertInstanceOf(Home::class, $home);

        // When we remove the "random existing home"
        $client->request(Request::METHOD_GET, '/homes');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->submitForm("Remove Home");

        // Then the "random existing home" is removed
        self::assertRouteSame('home_delete', ['id' => $home->getId()]);
        self::assertNull($this->getHomeRepository()->find($home->getId()));
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame('home_list');
        self::assertSelectorTextContains(
            'div.flash-notice',
            'Home ' . $home->getAddress() . ' ' . $home->getCity() . ' deleted with success.'
        );
    }

    private function getHomeRepository(): HomeRepository
    {
        /** @var HomeRepository */
        return static::getService(HomeRepository::class);
    }
}
