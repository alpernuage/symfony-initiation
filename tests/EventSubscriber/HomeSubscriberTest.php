<?php

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\HomeSubscriber;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class HomeSubscriberTest extends WebTestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertArrayHasKey(FormEvents::PRE_SUBMIT, HomeSubscriber::getSubscribedEvents());
    }

    public function testOnPreSubmitAddDepartmentCodeForParisCity(): void
    {
        $homeSubscriber = new HomeSubscriber();
        $formBuilder = $this->createMock(FormInterface::class);
        $formData = ['city' => 'Paris'];
        $event = new FormEvent($formBuilder, $formData);

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($homeSubscriber);
        $dispatcher->dispatch($event, FormEvents::PRE_SUBMIT);

        /** @var array<string> $updatedData */
        $updatedData = $event->getData();

        self::assertEquals('Paris (75)', $updatedData['city']);
    }
}
