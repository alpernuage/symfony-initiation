<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\User\Api\GetUserOutput;
use App\Repository\UserRepository;
use Symfony\Component\Uid\UuidV7;

final readonly class GetCollectionUserOutputProvider implements ProviderInterface
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    /**
     * @param UuidV7[] $uriVariables
     * @param GetCollection[] $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $users = $this->userRepository->findAll();
        $resources = [];

        foreach ($users as $user) {
            $resources[] = new GetUserOutput(
                $user->getFirstName(),
                $user->getLastName(),
                $user->getEmail(),
            );
        }

        return $resources;
    }
}
