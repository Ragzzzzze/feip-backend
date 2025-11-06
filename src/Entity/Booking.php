<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookingRepository;
use App\Enum\BookingStatus;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\Table(name: 'bookings')] 
class Booking {

    public function __construct() {
        $this->status = BookingStatus::PENDING;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?SummerHouse $house = null;

    #[ORM\Column(enumType: BookingStatus::class)]
    private ?BookingStatus $status = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    public function getId() : ?int { 
        return $this->id;
    }

    public function getUser() : ?User { 
        return $this->client;
    }

    public function getHouse() : ?SummerHouse { 
        return $this->house;
    }
    
    public function getStatus() : ?BookingStatus {
        return $this->status; 
    }

    public function getComment() : ?string { 
        return $this->comment; 
    }

    public function setId(?int $id) : static { 
        $this->id = $id;
        return $this->id; 
    }
    
    public function setUser(?User $client) : static { 
        $this->client = $client;
        return $this; 
    }

    public function setHouse(?SummerHouse $house) : static {
        $this->house = $house;
        return $this; 
    }

    public function setStatus(BookingStatus $status) : static { 
        $this->status = $status;
        return $this; 
    }

    public function setComment(string $comment) : static {
        $this->comment = $comment;
        return $this;
    }

}