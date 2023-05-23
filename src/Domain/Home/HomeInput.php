<?php

namespace App\Domain\Home;

use App\Entity\Home;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

class HomeInput
{
    public Uuid $id;

    #[Assert\NotBlank]
    public string $address = '';

    #[Assert\NotBlank]
    public string $city = '';

    #[Assert\NotBlank]
    public string $zipCode = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 2)]
    public string $country = '';

    #[Assert\Type(type: 'bool')]
    public bool $currentlyOccupied = true;

    #[Assert\NotBlank]
    public User $user;

    public function __construct()
    {
        $this->id = Uuid::v7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public static function createInputForUpdate(Home $home): self
    {
        $homeInput = new self();
        $homeInput->address = $home->getAddress();
        $homeInput->city = $home->getCity();
        $homeInput->zipCode = $home->getZipCode();
        $homeInput->country = $home->getCountry();
        $homeInput->currentlyOccupied = $home->isCurrentlyOccupied();
        $homeInput->user = $home->getUser();

        return $homeInput;
    }
}
