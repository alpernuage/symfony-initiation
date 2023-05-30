<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $testUser = new User(
            "John",
            "DOE",
            "test.user@example.com",
        );

        $manager->persist($testUser);

        for ($i = 0; $i < 10; $i++) {
            if ($i % 2 === 0) {
                $email = ($faker->safeEmail);
            } else {
                $email = null;
            }

            $user = new User(
                $faker->firstName,
                $faker->lastName,
                $email,
            );

            $manager->persist($user);
        }

        $manager->flush();
    }
}
