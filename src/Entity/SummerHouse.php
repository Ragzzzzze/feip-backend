<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SummerHouseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SummerHouseRepository::class)]
#[ORM\Table(name: 'houses')]
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
