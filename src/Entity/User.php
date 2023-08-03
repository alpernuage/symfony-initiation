<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Domain\User\Api\GetUserOutput;
use App\Domain\User\Api\PatchUserInput;
use App\Domain\User\Api\PostUserInput;
use App\Domain\User\Api\PutUserInput;
use App\Repository\UserRepository;
use App\State\GetCollectionUserOutputProvider;
use App\State\GetUserOutputProvider;
use App\State\PatchUserInputProcessor;
use App\State\PostUserInputProcessor;
use App\State\PutUserInputProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    operations: [
        new GetCollection(
            output: GetUserOutput::class,
            provider: GetCollectionUserOutputProvider::class
        ),
        new Get(
            output: GetUserOutput::class,
            provider: GetUserOutputProvider::class
        ),
        new Post(
            input: PostUserInput::class,
            processor: PostUserInputProcessor::class,
        ),
        new Put(
            input: PutUserInput::class,
            processor: PutUserInputProcessor::class,
        ),
        new Patch(
            input: PatchUserInput::class,
            processor: PatchUserInputProcessor::class,
        ),
        new Delete()
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
class User
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['user:read', 'user:write', 'home:item:get'])]
    private string $firstName;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['user:read', 'user:write', 'home:item:get'])]
    private string $lastName;

    #[ORM\Column(type: Types::STRING, length: 320, nullable: true)]
    #[Groups(['user:read', 'user:write', 'home:item:get'])]
    private ?string $email;

    public function __construct(string $firstName, string $lastName, ?string $email)
    {
        $this->id = Uuid::v7();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}
