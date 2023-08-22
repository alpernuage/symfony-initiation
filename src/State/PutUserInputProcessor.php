<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\User\Api\PutUserInput;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Uid\UuidV7;

final class PutUserInputProcessor implements ProcessorInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UserRepository $userRepository)
    {
    }

    /**
     * @param UuidV7[] $uriVariables
     * @param Put[] $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PutUserInput
    {
        /** @var User $user */
        $user = $this->userRepository->find($uriVariables['id']);

        /** @var PutUserInput $data */
        $user->setFirstName($data->firstName);
        $user->setLastName($data->lastName);

        if ($data->email !== $user->getEmail() && $data->email !== null) {
            $existingUser = $this->userRepository->findOneBy(['email' => $data->email]);

            if ($existingUser) {
                throw new UnprocessableEntityHttpException('Email already used.');
            }

            $user->setEmail($data->email);
        }

        $this->entityManager->flush();

        return $data;
    }
}
