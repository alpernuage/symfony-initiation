<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\HomeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: HomeRepository::class)]
#[ApiResource(
    description: 'A home is a place where a user lives',
    operations: [
        new GetCollection(),
        new Get(uriTemplate: '/home/{id}', normalizationContext: ['groups' => ['home:read', 'home:item:get']]),
        new Post(uriTemplate: '/home/create'),
        new Put(uriTemplate: '/home/edit/{id}'),
    ],
    formats: ['jsonld', 'json', 'html', 'jsonhal', 'csv' => ['text/csv']],
    normalizationContext: ['groups' => ['home:read']],
    denormalizationContext: ['groups' => ['home:write']],
    paginationItemsPerPage: 10
)]
#[ApiResource(
    uriTemplate: 'users/{user_id}/homes.{_format}',
    shortName: "User's Homes",
    operations: [new GetCollection()],
    uriVariables: [
        'user_id' => new Link(
            toProperty: 'user',
            fromClass: User::class,
        )
    ],
    normalizationContext: ['groups' => ['home:read']],
)]
#[ApiFilter(PropertyFilter::class)]
#[ApiFilter(SearchFilter::class, properties: ['user.lastName' => 'partial'])]
class Home
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['home:read', 'home:write'])]
    private string $address;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['home:read', 'home:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    private string $city;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['home:read', 'home:write'])]
    private string $zipCode;

    #[ORM\Column(type: Types::STRING, length: 2)]
    #[Groups('home:write')]
    private string $country;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['home:read', 'home:write'])]
    #[ApiFilter(BooleanFilter::class)]
    private bool $currentlyOccupied;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['home:read', 'home:write'])]
    private User $user;

    public function __construct(string $address, string $city, string $zipCode, string $country, bool $currentlyOccupied, User $user)
    {
        $this->id = Uuid::v7();
        $this->address = $address;
        $this->city = $city;
        $this->zipCode = $zipCode;
        $this->country = $country;
        $this->currentlyOccupied = $currentlyOccupied;
        $this->user = $user;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    #[Groups('home:read')]
    public function getShortAddress(): string
    {
        return u($this->address)->truncate(15, '...')->toString();
    }

    #[Groups('home:read')]
    public function getFullAddress(): string
    {
        return $this->address . ' ' . $this->zipCode . ' ' . $this->city;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function isCurrentlyOccupied(): bool
    {
        return $this->currentlyOccupied;
    }

    public function setCurrentlyOccupied(bool $currentlyOccupied): void
    {
        $this->currentlyOccupied = $currentlyOccupied;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
