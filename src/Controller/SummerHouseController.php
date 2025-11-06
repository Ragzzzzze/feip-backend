<?php

namespace App\Controller;

use App\Dto\SummerHouseDto;
use App\Services\SummerHouseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SummerHouseController extends AbstractController
{   
    public function __construct(
        private SummerHouseService $summerHouseService
    ) {}

    #[Route('/api/houses', name: 'get_summer_houses', methods : ['GET'])]
    public function get_summer_houses_cont(): JsonResponse
    {   
        try {
            $houses = $this->summerHouseService->getAllHouses();
            
            $housesArray = array_map(function($house) {
                return [
                    'id' => $house->getId(),
                    'name' => $house->getHouseName(),
                    'price' => $house->getPrice(),
                    'sleeps' => $house->getSleeps(),
                    'distance_to_sea' => $house->getDistanceToSea(),
                    'hasTV' => $house->getHasTV(),
                ];
            }, $houses);
            
            return new JsonResponse($housesArray);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to retrieve houses: ' . $e->getMessage()
            ], 500);
        }
    }
    

    #[Route('/api/available-houses', name: 'get_available_houses', methods : ['GET'])]
    public function get_available_houses_cont(): JsonResponse
    {   
        try {
            $houses = $this->summerHouseService->getAvailableHouses();
            
            $housesArray = array_map(function($house) {
                return [
                    'id' => $house->getId(),
                    'name' => $house->getHouseName(),
                    'price' => $house->getPrice(),
                    'sleeps' => $house->getSleeps(),
                    'distance_to_sea' => $house->getDistanceToSea(),
                    'hasTV' => $house->getHasTV()
                ];
            }, $houses);
            
            return new JsonResponse($housesArray);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to retrieve available houses: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/houses', name: 'create_house', methods: ['POST'])]
    public function create_house(Request $request): JsonResponse
    {
        $data = $request->toArray();
        
        if (empty($data)) {
            return new JsonResponse(["error" => "Request body is empty"], 422);
        }

        try {
            $houseDto = new SummerHouseDto(
                name: $data['name'],
                price: (float)$data['price'],
                sleeps: (int)$data['sleeps'],
                distanceToSea: (int)$data['distanceToSea'],
                hasTV: (bool)$data['hasTV']
            );
            
            $house = $this->summerHouseService->createHouse($houseDto);
            
            return new JsonResponse([
                "status" => "OK",
                "message" => "House created successfully",
                "house_id" => $house->getId()
            ], 201);
            
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                "error" => $e->getMessage()
            ], 400);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                "error" => "Failed to create house: " . $e->getMessage()
            ], 500);
        }
    }
}
        