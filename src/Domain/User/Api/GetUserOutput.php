<?php

declare(strict_types=1);

namespace App\Domain\User\Api;

use Symfony\Component\Serializer\Annotation\Groups;

final class GetUserOutput
{
    #[Groups(['user:read', 'user:write', 'home:item:get'])]
    public string $firstName;

    #[Groups(['user:read', 'user:write', 'home:item:get'])]
    public string $lastName;

    #[Groups(['user:read', 'user:write', 'home:item:get'])]
    public ?string $email = null;

    public function __construct(
        string  $firstName,
        string  $lastName,
        ?string $email = null,
    )
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
    }
}
