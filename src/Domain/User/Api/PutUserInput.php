<?php

declare(strict_types=1);

namespace App\Domain\User\Api;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class PutUserInput
{
    #[Assert\NotBlank(message: 'First name should not be blank.')]
    #[Assert\Length(max: 255, maxMessage: 'First name should not be longer than {{ limit }} characters.')]
    #[Groups(['user:read', 'user:write', 'home:item:get'])]
    public string $firstName = '';

    #[Assert\NotBlank(message: 'Last name should not be blank.')]
    #[Assert\Length(max: 255, maxMessage: 'Last name should not be longer than {{ limit }} characters.')]
    #[Groups(['user:read', 'user:write', 'home:item:get'])]
    public string $lastName = '';

    #[Assert\Email(message: 'Email should be valid.')]
    #[Assert\Length(max: 320, maxMessage: 'Email should not be longer than {{ limit }} characters.')]
    #[Groups(['user:read', 'user:write', 'home:item:get'])]
    public ?string $email = null;
}
