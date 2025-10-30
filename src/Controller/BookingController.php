<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Services\BookingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class BookingController extends AbstractController
{
    public function __construct(
        private BookingService $booking_service
    ) {}

    #[Route('api/booking', name: 'app_booking_create', methods: ["POST"])]
    public function app_booking_create(Request $request): Response {
        if (empty($request->toArray())) {
            return new JsonResponse(["error" => "Request body is empty"], 422);
        }

        $values = $request->toArray();

        try {
            $booking = new Booking(
                id: (int)$values["id"],
                house_id: (int)$values["house_id"],
                guest_name: $values["guest_name"],
                phone_number: $values["phone_number"],
                status: $values["status"] ?? "pending",
                comment: $values["comment"] ?? ""
            );
            
            $this->bookingService->create_booking($booking);
            
            return new JsonResponse([
                "status" => "OK",
                "message" => "Booking created successfully",
            ], 201);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                "error" => "Failed to create booking: " . $e->getMessage()
            ], 500);
        }
    }

    #[Route('api/booking', name:'app_booking_change_commentary', methods: ['PATCH'])]
    public function app_booking_change_commentary(Request $request): Response
    {
        if (empty($request->toArray())) {
            return new JsonResponse(["error" => "request body is empty"], 422);
        }

        $values = $request->toArray();

        try {
        $result = $this->bookingService->change_booking_commentary(
            (int)$values["id"], 
            $values["comment"]
        );
        
            if ($result) {
                return new JsonResponse([
                    "status" => "OK",
                    "message" => "Booking comment updated successfully"
                ], 200);
            } else {
                return new JsonResponse([
                    "error" => "Booking not found or update failed"
                ], 404);
            }
        
        } catch (\Exception $e) {
            return new JsonResponse([
                "error" => "Failed to update booking comment: " . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/bookings', name: 'app_booking_get', methods: ['GET'])]
    public function app_booking_get(): Response
    {
        try {
            $bookings = $this->bookingService->get_bookings();
            
            return new JsonResponse([
                'status' => 'OK',
                'count' => count($bookings),
                'bookings' => $bookings
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to retrieve bookings: ' . $e->getMessage()
            ], 500);
        }
    }
}
