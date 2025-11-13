<?php

namespace App\Controller;

use App\Entity\House;
use App\Services\SummerHouseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SummerHouseController extends AbstractController
{   
    public function __construct(
        private SummerHouseService $summer_house_service
    ) {}

    #[Route('/api/houses', name: 'get_summer_houses', methods : ['GET'])]
    public function get_summer_houses_cont(): Response
    {   
       $houses =  $this->summerHouseService->get_summer_houses();
        return new JsonResponse($houses);
    }

    #[Route('/api/available-houses', name: 'get_summer_houses', methods : ['GET'])]
    public function get_available_houses_cont(): Response
    {   
       $houses =  $this->summerHouseService->get_available_houses();
        return new JsonResponse($houses);
    }
}
