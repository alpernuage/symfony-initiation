<?php

namespace App\EventListener;

use App\Entity\Home;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postUpdate, method: 'updateHomeOnUserUpdate', entity: User::class)]
class UserListener
{
    public function updateHomeOnUserUpdate(User $user, PostUpdateEventArgs $event): void
    {
        $objectManager = $event->getObjectManager();
        $homes = $objectManager->getRepository(Home::class)->findBy(['user' => $user]);

        foreach ($homes as $home) {
            $home->setUpdatedAt($user->getUpdatedAt());
            $event->getObjectManager()->persist($home);
        }
        $event->getObjectManager()->flush();
    }
}
