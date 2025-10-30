<?php

namespace App\Entity;

class House {
    public function __construct (
        public int $id,
        public string $name,
        public int $price,
        public int $sleeps,
        public int $distance_to_sea,
        public string $amenities,
        public bool $is_available
    ) {}

    public function to_array () : array {
        return array(
            $this->id,
            $this->name,
            $this->price,
            $this->sleeps,
            $this->distance_to_sea,
            $this->amenities,
            $this->is_available
        );
    }
}