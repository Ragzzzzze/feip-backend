<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')] 
class User {

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

    public function getId() : ?int { 
        return $this->id; 
    }

    public function getName() : ?string { 
        return $this->name; 
    }

    public function getPhoneNumber() : ?string { 
        return $this->phoneNumber; 
    }

    public function getBookings() : Collection { 
        return $this->bookings; 
    }
    
    public function setName(?string $name) : static { 
        $this->name = $name;
        return $this; 
    }

    public function setPhoneNumber(?string $phoneNumber) : static {
        $this->phoneNumber = $phoneNumber;
        return $this; 
    }

    public function addBooking(Booking $booking) : static { 
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setUser($this);
        }
        return $this;
    }

    public function removeBooking(Booking $booking) : static { 
        if ($this->bookings->removeElement($booking)) {
            if ($booking->getUser() === $this) {
                $booking->setUser(null);
            }
        }
        return $this;
    }
}