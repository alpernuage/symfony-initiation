<?php

namespace App\Domain\User;

use App\Entity\User;
use App\Validator\UniqueField;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

#[UniqueField(User::class, 'email')]
class UserInput
{
    public Uuid $id;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $firstName = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $lastName = '';

    #[Assert\Email]
    #[Assert\Length(max: 320)]
    public ?string $email = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public static function createInputForUpdate(User $user): self
    {
        $userInput = new self();
        $userInput->id = $user->getId();
        $userInput->firstName = $user->getFirstName();
        $userInput->lastName = $user->getLastName();
        $userInput->email = $user->getEmail();

        return $userInput;
    }
}
