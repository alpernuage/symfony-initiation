<?php

namespace App\Domain\User;

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
}
