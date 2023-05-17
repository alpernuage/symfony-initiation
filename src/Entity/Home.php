<?php

namespace App\Entity;

use App\Repository\HomeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: HomeRepository::class)]
class Home
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    #[ORM\Column(type: Types::TEXT)]
    private string $address;

    #[ORM\Column(type: Types::TEXT)]
    private string $city;

    #[ORM\Column(type: Types::TEXT)]
    private string $zipCode;

    #[ORM\Column(type: Types::STRING, length: 2)]
    private string $country;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $currentlyOccupied;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
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
