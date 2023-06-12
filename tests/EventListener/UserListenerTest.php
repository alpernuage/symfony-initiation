<?php

namespace App\Tests\EventListener;

use App\Entity\Home;
use App\Entity\User;
use App\Repository\HomeRepository;
use App\Repository\UserRepository;
use App\Tests\TranslatorTrait;
use App\Tests\WebTestTrait;
use App\Tests\DataProviderTrait;
use Doctrine\ORM\Events;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use App\EventListener\UserListener;

class UserListenerTest extends WebTestCase
{
    use DataProviderTrait;
    use TranslatorTrait;
    use WebTestTrait;

    /**
     * @dataProvider languagesProvider
     */
    public function testUpdateHomeOnUserUpdate(string $locale): void
    {
        // Given the "John" user is created
        $client = static::createClient();
        $testUser = static::getTestUser();
        $dispatcher = new EventDispatcher();

        /** @var UserRepository $userRepository */
        $userRepository = static::getService(UserRepository::class);
        $homeRepository = static::getService(HomeRepository::class);

        // When we update the "John" user with the following data
        $client->request(Request::METHOD_GET, sprintf('/%s/user/edit/' . $testUser->getId(), $locale));

        $callable = [new UserListener(), 'updateHomeOnUserUpdate'];
        $dispatcher->addListener(Events::postUpdate, $callable);

        $saveButton = self::getTranslator()->trans('buttons.save', locale: $locale);
        $client->submitForm($saveButton, [
            'user_form[firstName]' => 'Pierre',
            'user_form[lastName]' => 'BLANC',
            'user_form[email]' => '',
        ]);

        /** @var User $updatedUser */
        $updatedUser = $userRepository->find($testUser->getId());

        // Then the Homes of the "John" user are also updated
        /** @var HomeRepository $homeRepository */
        /** @var Home $home */
        $home = $homeRepository->findOneBy(['user' => $testUser]);

        $listeners = $dispatcher->getListeners(Events::postUpdate);

        self::assertTrue(in_array($callable, $listeners, true));
        self::assertTrue($dispatcher->hasListeners(Events::postUpdate));
        self::assertEquals($updatedUser->getUpdatedAt(), $home->getUpdatedAt());
    }
}
