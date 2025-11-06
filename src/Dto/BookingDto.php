<?php

namespace App\Dto;


class BookingDto {
    public function __construct(
        public string $phoneNumber,
        public int $houseId,
        public ?string $comment,
    ) {}
}
