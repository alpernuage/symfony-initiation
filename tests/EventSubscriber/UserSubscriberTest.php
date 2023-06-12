<?php

namespace App\Tests\EventSubscriber;

use App\Controller\UserController;
use App\EventSubscriber\UserSubscriber;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Tests\DataProviderTrait;

class UserSubscriberTest extends WebTestCase
{
    use DataProviderTrait;

    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey(KernelEvents::REQUEST, UserSubscriber::getSubscribedEvents());
        self::assertArrayHasKey(KernelEvents::RESPONSE, UserSubscriber::getSubscribedEvents());
        self::assertArrayHasKey(KernelEvents::CONTROLLER, UserSubscriber::getSubscribedEvents());
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testOnRequestAddRecentlyVisitedPagesFlash(string $locale): void
    {
        $request = Request::create(sprintf('/%s/users', $locale));
        $flashBag = new FlashBag();

        $requestEvent = new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);

        $session = $this->createMock(FlashBagAwareSessionInterface::class);
        $session->method('getFlashBag')->willReturn($flashBag);

        $request->setSession($session);

        $userSubscriber = new UserSubscriber();

        $this->dispatcher->addSubscriber($userSubscriber);
        $this->dispatcher->dispatch($requestEvent, KernelEvents::REQUEST);

        self::assertContains(sprintf('http://localhost/%s/users', $locale), $flashBag->get('recently-visited-pages'));
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testOnResponseAddCookie(string $locale): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $client = static::createClient();
        $requestType = HttpKernelInterface::MAIN_REQUEST;

        $client->request('GET', sprintf('/%s/users', $locale));
        $response = $client->getResponse();
        $request = $client->getRequest();

        $responseEvent = new ResponseEvent($kernel, $request, $requestType, $response);
        $cookies = $response->headers->getCookies();
        $firstCookie = $cookies[0];

        $userSubscriber = new UserSubscriber();
        $this->dispatcher->addSubscriber($userSubscriber);
        $this->dispatcher->dispatch($responseEvent, KernelEvents::RESPONSE);

        self::assertNotEmpty($cookies);
        self::assertResponseCookieValueSame('my_cookie', 'response_cookie_value');
        self::assertResponseHasCookie('my_cookie');
        self::assertEquals('my_cookie', $firstCookie->getName());
        self::assertEquals('response_cookie_value', $firstCookie->getValue());
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testOnUserCreateAndUpdateAddMessageFlash(string $locale): void
    {
        $request = Request::create(sprintf('/%s/users', $locale), Request::METHOD_POST);
        $flashBag = new FlashBag();

        $callable = [new UserController(), 'create'];

        $controllerEvent = new ControllerEvent($this->createMock(HttpKernelInterface::class), $callable, $request, HttpKernelInterface::MAIN_REQUEST);

        $session = $this->createMock(FlashBagAwareSessionInterface::class);
        $session->method('getFlashBag')->willReturn($flashBag);

        $request->setSession($session);

        $userSubscriber = new UserSubscriber();

        $this->dispatcher->addSubscriber($userSubscriber);
        $this->dispatcher->dispatch($controllerEvent, KernelEvents::CONTROLLER);

        $expectedFlashMessage = 'User created';
        self::assertContains($expectedFlashMessage, $flashBag->get('create-listener'));
    }
}
