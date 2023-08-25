<?php

namespace App\DataFixtures;

use App\Factory\SecurityUserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SecurityUserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        SecurityUserFactory::createOne(['email' => 'admin@example.com']);
        SecurityUserFactory::createMany(10);
    }
}
