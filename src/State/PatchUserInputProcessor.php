<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\User\Api\PatchUserInput;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\UuidV7;

final class PatchUserInputProcessor implements ProcessorInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UserRepository $userRepository)
    {
    }

    /**
     * @param UuidV7[] $uriVariables
     * @param Patch[] $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PatchUserInput
    {
        /** @var User $user */
        $user = $this->userRepository->find($uriVariables['id']);

        /** @var PatchUserInput $data */
        if ($data->firstName !== null) {
            $user->setFirstName($data->firstName);
        }

        if ($data->lastName !== null) {
            $user->setLastName($data->lastName);
        }

        if ($data->email !== null) {
            $user->setEmail($data->email);
        }

        $this->entityManager->flush();

        return $data;
    }
}
