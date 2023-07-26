<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Home;
use App\Entity\User;
use App\Repository\HomeRepository;
use App\Repository\UserRepository;
use App\Tests\WebTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HomeApiTest extends ApiTestCase
{
    use WebTestTrait;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testGetHomes(): void
    {
        // Given 31 homes are created
        $existingHomes = $this->getHomeRepository()->findAll();
        $existingHomeIds = $this->getExistingHomeIds($existingHomes);
        $testHome = $this->getTestHome();

        // When the home collection is requested
        $response = $this->client->request(Request::METHOD_GET, '/api/homes');
        $allReturnedHomes = $this->getAllReturnedHomes($response);
        $responseArray = $this->getResponseContent($response);
        $returnedHomeIds = $this->getReturnedHomeIds($allReturnedHomes);

        $totalItems = (int)$responseArray['hydra:totalItems'];

        // Then 31 homes are returned including testHome
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertArrayHasKey('hydra:member', $responseArray);
        self::assertSame($returnedHomeIds, $existingHomeIds);
        self::assertArrayHasKey('@type', $allReturnedHomes[0]);
        self::assertSame(31, $totalItems);
        self::assertSame('Home', $allReturnedHomes[0]['@type']);
        self::assertTrue($this->isTestHomeInReturnedHomes($allReturnedHomes, $testHome));
    }

    public function testCreateHome(): void
    {
        // Given the "75, rue Auguste Hamel Lacroix-Sur-Mer" home isn't created
        $notCreatedHome = $this->getHomeRepository()->findOneBy([
            "address" => "75, rue Auguste Hamel",
            "city" => "Lacroix-Sur-Mer",
            "zipCode" => "61 868",
            "country" => "LV",
            "currentlyOccupied" => true,
            "user" => static::getTestUser()->getId()
        ]);

        // When we create the "75, rue Auguste Hamel Lacroix-Sur-Mer" home
        $payload = [
            "address" => "75, rue Auguste Hamel",
            "city" => "Lacroix-Sur-Mer",
            "zipCode" => "61 868",
            "country" => "LV",
            "currentlyOccupied" => true,
            "user" => "/api/users/" . static::getTestUser()->getId()
        ];

        $response = $this->client->request(
            Request::METHOD_POST,
            '/api/home/create',
            ['json' => $payload]
        );
        $responseArray = $this->getResponseContent($response);
        $stringToExplode = $responseArray['@id'];
        $homeId = explode("/api/home/", $stringToExplode)[1];
        $homeRepository = $this->getHomeRepository();
        $createdHome = $homeRepository->find($homeId);

        // Then the "75, rue Auguste Hamel Lacroix-Sur-Mer" home is successfully created
        self::assertNull($notCreatedHome);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertArrayHasKey('@id', $responseArray);
        self::assertInstanceOf(Home::class, $createdHome);
        self::assertEquals("75, rue Auguste Hamel", $createdHome->getAddress());
        self::assertEquals("Lacroix-Sur-Mer", $createdHome->getCity());
    }

    public function testFindHomeByIri(): void
    {
        // Given "existing home" home has been created
        $testHome = $this->getTestHome();

        // When we request the "existing home" home
        $iri = $this->findIriBy(Home::class, ['address' => $testHome->getAddress()]);

        $this->client->request(Request::METHOD_GET, $iri);

        // Then the "existing home" home is successfully returned
        self::assertResponseIsSuccessful();
        self::assertSame($iri, '/api/home/' . $testHome->getId());
    }

    public function testGetHome(): void
    {
        // Given "existing home" has been created
        $testHome = $this->getTestHome();

        // When we call the "existing home"
        $response = $this->client->request(Request::METHOD_GET, '/api/home/' . $testHome->getId());
        $responseArray = $this->getResponseContent($response);

        // Then the "existing home" is successfully returned
        self::assertResponseIsSuccessful();
        self::assertEquals('/api/contexts/Home', $responseArray['@context']);
        self::assertEquals('/api/home/' . $testHome->getId(), $responseArray['@id']);
        self::assertEquals($testHome->getAddress(), $responseArray['address']);
    }

    public function testGetNonExistentHome(): void
    {
        // Given a non-existent home id
        $nonexistentHomeId = '0000a000-00a0-0a0a-0a0a-000aa0a0000a';

        // When we get home with non-existent id
        $response = $this->client->request(Request::METHOD_GET, '/api/home/' . $nonexistentHomeId);
        $responseArray = $this->getResponseContent($response);

        // Then the "non-existent home" is not found
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertEquals('/api/contexts/Error', $responseArray['@context']);
        self::assertEquals('hydra:Error', $responseArray['@type']);
        self::assertJsonContains(['hydra:description' => 'Not Found']);
    }

    public function testUpdateHome(): void
    {
        // Given "existing home" has been created
        $testHome = $this->getTestHome();

        // When we update the "existing home" with the following data
        $payload = [
            "address" => "11, Avenue de l'Europe",
            "city" => "Paris",
            "zipCode" => "61 868",
            "country" => "LV",
            "currentlyOccupied" => true,
            "user" => "/api/users/" . static::getTestUser()->getId()
        ];

        $this->client->request(
            Request::METHOD_PUT,
            'api/home/edit/' . $testHome->getId(),
            ['json' => $payload]
        );

        // Then the "existing home" is successfully updated
        self::assertResponseIsSuccessful();

        // And we find the "existing home" with the same "{id}" and the updated data
        $updatedHome = $this->getHomeRepository()->find($testHome->getId());

        self::assertInstanceOf(Home::class, $testHome);
        self::assertNotEquals('75, rue Auguste Hamel', $testHome->getAddress());
        self::assertNotEquals('Lacroix-Sur-Mer', $testHome->getCity());

        self::assertEquals("11, Avenue de l'Europe", $updatedHome->getAddress());
        self::assertEquals('Paris', $updatedHome->getCity());
    }

    public function testSearchHomesByCity(): void
    {
        // Given an existing city name
        $home = $this->getTestHome();
        $city = $home->getCity();

        // When searching homes by city
        $response = $this->client->request(Request::METHOD_GET, '/api/homes?city=' . $city);
        $responseArray = $this->getResponseContent($response);

        /** @var array<array<string>> $homes */
        $homes = $responseArray['hydra:member'];

        // Then the homes having the given city are found
        self::assertResponseIsSuccessful();
        self::assertNotEmpty($homes);

        foreach ($homes as $oneHome) {
            self::assertEquals($city, $oneHome['city']);
        }
    }

    public function testFilterHomesByOccupiedStatus(): void
    {
        // Given occupied homes are created
        $existingOccupiedHomes = $this->getHomeRepository()->findBy(['currentlyOccupied' => true]);
        $existingHomeIds = $this->getExistingHomeIds($existingOccupiedHomes);

        // When filtering homes by occupied status
        $response = $this->client->request(Request::METHOD_GET, '/api/homes?currentlyOccupied=true');
        $responseArray = $this->getResponseContent($response);
        $allReturnedHomes = $this->getAllReturnedHomes($response);
        $returnedHomeIds = $this->getReturnedHomeIds($allReturnedHomes);

        /** @var array<array<string>> $returnedOccupiedHomes */
        $returnedOccupiedHomes = $responseArray['hydra:member'];

        // Then the occupied homes are found
        self::assertResponseIsSuccessful();
        self::assertNotEmpty($returnedOccupiedHomes);
        self::assertSame($returnedHomeIds, $existingHomeIds);

        foreach ($returnedOccupiedHomes as $home) {
            self::assertTrue((bool)$home['currentlyOccupied']);
        }
    }

    public function testFilterHomesByUserLastName(): void
    {
        // Given users having "DOE" last name are created
        /** @var UserRepository $userRepository */
        $userRepository = static::getService(UserRepository::class);
        $usersHavingDoeLastName = $userRepository->findBy(['lastName' => "DOE"]);

        // When filtering homes by the last name "DOE"
        $response = $this->client->request(Request::METHOD_GET, '/api/homes?user.lastName=DOE');
        $responseArray = $this->getResponseContent($response);

        /** @var array<array<string>> $homes */
        $homes = $responseArray['hydra:member'];

        // Then the homes of the users having "DOE last name are found
        self::assertResponseIsSuccessful();
        self::assertNotEmpty($homes);

        foreach ($homes as $home) {
            $stringToExplode = $home['user'];
            $userId = explode("/api/users/", $stringToExplode)[1];

            /** @var User $user */
            $user = $userRepository->find($userId);

            self::assertContains($user, $usersHavingDoeLastName);
            self::assertEquals($user->getLastName(), "DOE");
        }
    }

    public function testPaginateHomes(): void
    {
        // Given 31 homes are created
        $existingHomes = $this->getHomeRepository()->findAll();
        $existingHomeIds = $this->getExistingHomeIds($existingHomes);

        $pageNumber = 3;

        // When paginating homes
        $response = $this->client->request(Request::METHOD_GET, '/api/homes?page=' . $pageNumber);
        $responseArray = $this->getResponseContent($response);

        /** @var array<array<string>> $homesOnPage */
        $homesOnPage = $responseArray['hydra:member'];

        $returnedHomeIds = $this->getReturnedHomeIds($homesOnPage);
        $commonValues = array_intersect($existingHomeIds, $returnedHomeIds);

        /** @var array<array<string>> $paginationInfos */
        $paginationInfos = $responseArray['hydra:view'];

        // Then the result is paginated
        self::assertResponseIsSuccessful();
        self::assertCount(10, $homesOnPage);
        self::assertEquals('/api/homes?page=' . $pageNumber, $paginationInfos['@id']);
        self::assertEquals('/api/homes?page=1', $paginationInfos['hydra:first']);
        self::assertEquals('/api/homes?page=2', $paginationInfos['hydra:previous']);
        self::assertEquals('/api/homes?page=4', $paginationInfos['hydra:next']);
        self::assertSame(array_values($returnedHomeIds), array_values($commonValues));
    }

    public function testGetUserHomes(): void
    {
        // Given the "John DOE" user
        $user = static::getTestUser();

        // When getting homes for the "John DOE" user
        $response = $this->client->request(Request::METHOD_GET, '/api/users/' . $user->getId() . '/homes');
        $responseArray = $this->getResponseContent($response);

        /** @var array<array<string>> $homes */
        $homes = $responseArray['hydra:member'];

        self::assertResponseIsSuccessful();
        self::assertArrayHasKey('hydra:member', $responseArray);
        self::assertNotEmpty($homes);
        self::assertArrayHasKey('@type', $homes[0]);

        foreach ($homes as $home) {
            $stringToExplode = $home['user'];
            $userId = explode("/api/users/", $stringToExplode)[1];

            self::assertEquals($user->getId(), $userId);
        }
    }

    private function getHomeRepository(): HomeRepository
    {
        return static::getService(HomeRepository::class);
    }

    private function getTestHome(): Home
    {
        /** @var Home */
        return $this->getHomeRepository()->findOneBy(
            [
                'address' => '1 rue de la Course',
                'city' => 'Aix-en-Provence',
                'zipCode' => '12345',
                'country' => 'FR',
                'currentlyOccupied' => true,
                'user' => static::getTestUser(),
            ]);
    }

    /**
     * @return array<string>
     */
    private
    function getResponseContent(ResponseInterface $response): array
    {
        return $response->toArray(false);
    }

    /**
     * @param array<Home> $homes
     * @return array<string>
     */
    private
    function getExistingHomeIds(array $homes): array
    {
        return array_map(function ($home) {
            return strval($home->getId());
        }, $homes);
    }

    /**
     * @param array<array<string>> $returnedHomes
     * @return array<string>
     */
    private
    function getReturnedHomeIds(array $returnedHomes): array
    {
        return array_map(function ($home) {
            return basename($home['@id']);
        }, $returnedHomes);
    }

    /**
     * @param ResponseInterface $response
     * @return array<array<string>>
     */
    private
    function getAllReturnedHomes(ResponseInterface $response): array
    {
        $responseArray = $this->getResponseContent($response);

        /** @var array<array<string>> $allReturnedHomes */
        $allReturnedHomes = $responseArray['hydra:member'];

        while (isset($responseArray['hydra:view']['hydra:next'])) {
            $nextPageUrl = $responseArray['hydra:view']['hydra:next'];
            $response = $this->client->request(Request::METHOD_GET, $nextPageUrl);
            $responseArray = $this->getResponseContent($response);

            /** @var array<array<string>> $returnHomesOnThePage */
            $returnHomesOnThePage = $responseArray['hydra:member'];

            $allReturnedHomes = array_merge($allReturnedHomes, $returnHomesOnThePage);
        }

        return $allReturnedHomes;
    }

    /**
     * @param array<array<string>> $returnedHomes
     * @param Home $testHome
     * @return bool
     */
    private
    function isTestHomeInReturnedHomes(array $returnedHomes, Home $testHome): bool
    {
        foreach ($returnedHomes as $home) {
            if ($home['@id'] === '/api/home/' . $testHome->getId()) {
                return true;
            }
        }

        return false;
    }
}
