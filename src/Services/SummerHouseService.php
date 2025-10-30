<?php

namespace App\Services;

use App\Entity\House;

class SummerHouseService {
    public function __construct (private string $filepath) {

    }

    public function get_summer_houses() : array {
        $houses = [];
        
        if (!file_exists($this->filepath)) {
            return $houses;
        }
        
        $file = fopen($this->filepath, "r");

        while (($data = fgetcsv($file)) !== FALSE) {

            $houses[] = new House(
                id: (int)$data[0],
                name: $data[1],
                price: (int)$data[2],
                sleeps: (int)$data[3],
                distance_to_sea: (int)$data[4],
                amenities: $data[5],
                is_available: (bool)$data[6]
            );
        }

        fclose($file);
        return $houses;
    }

    public function get_available_houses(): array {
        $allHouses = $this->get_summer_houses();
        $availableHouses = [];
        
        foreach ($allHouses as $house) {
            if ($house->is_available) {
                $availableHouses[] = $house;
            }
        }
        
        return $availableHouses;
    }
}
