<?php

namespace App\Domain\User;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class UserInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $firstName = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $lastName = '';

    #[Assert\Email]
    #[Assert\Length(max: 320)]
    public ?string $email = null;

    public static function  createInputForUpdate(User $user): self
    {
        $userInput = new self();
        $userInput->firstName = $user->getFirstName();
        $userInput->lastName = $user->getLastName();
        $userInput->email = $user->getEmail();

        return $userInput;
    }
}
