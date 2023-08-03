<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\User\Api\PostUserInput;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\UuidV7;

/**
 * @param PostUserInput $data
 */
final class PostUserInputProcessor implements ProcessorInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param UuidV7[] $uriVariables
     * @param Post[] $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PostUserInput
    {
        /** @var PostUserInput $data */
        $user = new User(
            $data->firstName,
            $data->lastName,
            $data->email,
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $data;
    }
}
