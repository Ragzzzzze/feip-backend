<?php

namespace App\Dto;

class SummerHouseDto {
    public function __construct(
        public string $name,
        public int $price,
        public int $sleeps,
        public int $distanceToSea,
        public string $hasTV,
    ) {}
}