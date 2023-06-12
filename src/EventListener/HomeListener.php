<?php

namespace App\EventListener;

use App\Entity\Home;
use App\Repository\HomeRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

#[AsEventListener(event: ControllerEvent::class, method: 'onShowPageDisplayCountryName')]
class HomeListener
{
    public function __construct(private readonly HomeRepository $homeRepository)
    {
    }

    public function onShowPageDisplayCountryName(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_route') === 'home_show') {

            /** @var Home $home */
            $home = $this->homeRepository->find($request->attributes->get('id'));
            $request->attributes->set('countryName', $home->getCountry());
        }
    }
}
