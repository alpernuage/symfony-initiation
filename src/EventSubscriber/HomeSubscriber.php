<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HomeSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmitAddDepartmentCodeForParisCity',
        ];
    }

    public function onPreSubmitAddDepartmentCodeForParisCity(FormEvent $event): void
    {
        /** @var array<string> $data */
        $data = $event->getData();

        if (isset($data['city']) && $data['city'] === 'Paris') {
            $data['city'] = 'Paris (75)';
            $event->setData($data);
        }
    }
}
