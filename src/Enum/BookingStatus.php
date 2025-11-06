<?php

namespace App\Enum;

enum BookingStatus: string {
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Ожидание',
            self::CONFIRMED => 'Подтвержден',
            self::CANCELLED => 'Отменен'
        };
    }
}