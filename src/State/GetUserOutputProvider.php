<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\User\Api\GetUserOutput;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\UuidV7;

final readonly class GetUserOutputProvider implements ProviderInterface
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    /**
     * @param UuidV7[] $uriVariables
     * @param Get[] $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): GetUserOutput|Response|null
    {
        /** @var User|null $user */
        $user = $this->userRepository->find($uriVariables['id']);

        if ($user === null) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }

        return new GetUserOutput($user->getFirstName(), $user->getLastName(), $user->getEmail());
    }
}
