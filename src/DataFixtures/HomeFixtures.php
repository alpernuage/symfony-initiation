<?php

namespace App\DataFixtures;

use App\Entity\Home;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class HomeFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $users = $this->userRepository->findBy([], limit: 10);

        foreach ($users as $user) {
            for ($i = 0; $i < 3; $i++) {
                $home = new Home(
                    $faker->streetAddress,
                    $faker->city,
                    $faker->postcode,
                    $faker->countryCode,
                    $faker->boolean,
                    $user
                );

                $manager->persist($home);
            }
        }

        $manager->flush();
    }
}
