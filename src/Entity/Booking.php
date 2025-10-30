<?php

namespace App\Entity;

class Booking {
    public function __construct (
        public int $id,
        public int $house_id,
        public string $guest_name,
        public string $phone_number,
        public string $status,
        public string $comment,
    ) {}

    public function to_array () : array {
        return array(
            $this->id,
            $this->house_id,
            $this->guest_name,
            $this->phone_number,
            $this->status,
            $this->comment
        );
    }
    
}