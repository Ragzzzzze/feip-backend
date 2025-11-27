<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 12, unique: true)]
    private ?string $phoneNumber = null;

    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'client', orphanRemoval: true)]
    private Collection $bookings;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function getRoles(): array 
    {
        $roles = $this->roles;
        if (empty($this->roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function getPassword() : ?string
    {
        return $this->password;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function setRoles(array $roles) : static
    {
        $this->roles = $roles;

        return $this;
    }

    public function setPassword(?string $password) : static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        return;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->phoneNumber;
    }
}
