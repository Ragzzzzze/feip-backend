<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SummerHouseRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\BookingController;

#[ORM\Entity(repositoryClass: SummerHouseRepository::class)]
#[ORM\Table(name: 'houses')]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/houses',
            controller: HouseController::class . '::getSummerHousesCont',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")'
        ),
        new GetCollection(
            uriTemplate: '/houses/available_houses',
            controller: HouseController::class . '::getAvailableHousesCont',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")'
        ),
        new Post(
            uriTemplate: '/houses/create',
            controller: HouseController::class . '::createHouse',
            security: 'is_granted("IS_AUTHENTICATED_FULLY")'
        ),
    ]
)]
class SummerHouse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $houseName = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?int $sleeps = null;

    #[ORM\Column]
    private ?int $distanceToSea = null;

    #[ORM\Column]
    private bool $hasTV = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHouseName(): ?string
    {
        return $this->houseName;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function getDistanceToSea(): ?int
    {
        return $this->distanceToSea;
    }

    public function getSleeps(): ?int
    {
        return $this->sleeps;
    }

    public function getHasTV(): bool
    {
        return $this->hasTV;
    }

    public function setHouseName(string $houseName): static
    {
        $this->houseName = $houseName;

        return $this;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function setSleeps(int $sleeps): static
    {
        $this->sleeps = $sleeps;

        return $this;
    }

    public function setDistanceToSea(int $distanceToSea): static
    {
        $this->distanceToSea = $distanceToSea;

        return $this;
    }

    public function setHasTV(bool $hasTV): static
    {
        $this->hasTV = $hasTV;

        return $this;
    }
}
