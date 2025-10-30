<?php

namespace App\Services;

use App\Entity\Booking;

class BookingService {
    public function __construct (private string $filepath) {

    }

    private function get_bookings(): array {
        $bookings = [];
        
        if (!file_exists($this->filepath)) {
            return $bookings;
        }

        $file = fopen($this->filepath, "r");
        
            
        while (($data = fgetcsv($file)) !== FALSE) {

            $bookings[] = new Booking(
                id: (int)$data[0],
                house_id: (int)$data[1],
                guest_name: $data[2],
                phone_number: $data[3],
                status: $data[4],
                comment: $data[5]
            );
        }
        fclose($file);
        
        return $bookings;

    }
    
    public function create_booking (Booking $booking) : bool {
        $file = fopen($this->filepath, "a");

        if ($file == false) {
            return false;
        }
        

        $succes = fputcsv($file, $booking->to_array());
        fclose($file);
        
        return $succes !== false;
    }

    public function change_booking_commentary (Booking $booking, string $new_comment) : bool {
        $bookings = $this->get_bookings();
        $updated = false;
        
        foreach ($bookings as $booking) {
            if ($booking->id === $bookingId) {
                $booking->comment = $new_Comment;
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            $file = fopen($this->filepath, "w");
            $booking_arary = $booking->to_array();

            fputcsv($file, $booking_arary);

            fclose($file);
        }
        
        return false;


    }
}