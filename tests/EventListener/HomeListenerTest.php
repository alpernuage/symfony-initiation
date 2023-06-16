<?php

namespace App\Tests\EventListener;

use App\Entity\Home;
use App\EventListener\HomeListener;
use App\Repository\HomeRepository;
use App\Tests\TranslatorTrait;
use App\Tests\WebTestTrait;
use App\Tests\DataProviderTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Intl\Countries;

class HomeListenerTest extends WebTestCase
{
    use DataProviderTrait;
    use TranslatorTrait;
    use WebTestTrait;

    /**
     * @dataProvider languagesProvider
     */
    public function testOnShowPageDisplayCountryName(string $locale): void
    {
        $client = static::createClient();

        /** @var HomeRepository $homeRepository */
        $homeRepository = static::getService(HomeRepository::class);

        /** @var Home $home */
        $home = $homeRepository->findOneBy([]);

        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = static::getService(EventDispatcherInterface::class);

        $countryLabel = self::getTranslator()->trans('home.country', locale: $locale) . ':';
        $displayedCountryName = Countries::getName($home->getCountry(), $locale);
        $callable = [new HomeListener($homeRepository), 'onShowPageDisplayCountryName'];
        $listeners = $dispatcher->getListeners(KernelEvents::CONTROLLER);

        $client->request(Request::METHOD_GET, sprintf('/%s/home/' . $home->getId(), $locale));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertRouteSame('home_show', ['id' => $home->getId()]);
        self::assertTrue(in_array($callable, $listeners));
        self::assertTrue($dispatcher->hasListeners(KernelEvents::CONTROLLER));
        self::assertSelectorTextContains('p strong', $countryLabel);
        self::assertSelectorTextContains('p', $displayedCountryName);
    }
}
