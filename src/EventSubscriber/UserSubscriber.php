<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Cookie;

class UserSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequestAddRecentlyVisitedPagesFlash',
            KernelEvents::RESPONSE => 'onResponseAddCookie',
            KernelEvents::CONTROLLER => 'onUserCreateAndUpdateAddMessageFlash',
        ];
    }

    public function onRequestAddRecentlyVisitedPagesFlash(RequestEvent $event): void
    {
        /** @var Session $session */
        $session = $event->getRequest()->getSession();
        $url = $event->getRequest()->getUri();
        $session->getFlashBag()->add('recently-visited-pages', $url);
    }

    public function onResponseAddCookie(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->setCookie(new Cookie('my_cookie', 'response_cookie_value'));
        $event->setResponse($response);
    }

    public function onUserCreateAndUpdateAddMessageFlash(ControllerEvent $event): void
    {
        /** @var Session $session */
        $session = $event->getRequest()->getSession();
        $requestUri = $event->getRequest()->getRequestUri();

        if (str_contains($requestUri, '/api')) {
            return;
        }

        /** @var array<string> $controllerCallable */
        $controllerCallable = $event->getController();
        $method = $controllerCallable[1];

        if ($event->getRequest()->getMethod() === Request::METHOD_POST) {
            if ($method === 'create') {
                $session->getFlashBag()->add('create-listener', 'User created');
            }
            if ($method === 'edit') {
                $session->getFlashBag()->add('update-listener', 'User updated');
            }
        }
    }
}
