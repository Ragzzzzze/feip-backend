<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\BookingDto;
use App\Services\BookingService;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    public function __construct(
        private BookingService $bookingService,
    ) {
    }

    #[Route('api/booking', name: 'app_booking_create', methods: ['POST'])]
    public function appBookingCreate(Request $request): JsonResponse
    {
        if (empty($request->toArray())) {
            return new JsonResponse(['error' => 'Request body is empty'], 422);
        }

        $data = $request->toArray();

        try {
            if (!houseId) {
                return $this->json(['error' => 'Missing field: houseId'], 400);
            }
            $bookingDto = new BookingDto(
                phoneNumber: $data['phoneNumber'] ?? '',
                houseId: (int) $data['houseId'],
                comment: $data['comment'] ?? null
            );

            $booking = $this->bookingService->createBooking($bookingDto);

            return new JsonResponse([
                'status' => 'OK',
                'message' => 'Booking created successfully',
                'booking_id' => $booking->getId(),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 400);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to create booking: '.$e->getMessage(),
            ], 500);
        }
    }

    #[Route('api/booking', name: 'app_booking_change_commentary', methods: ['PATCH'])]
    public function appBookingChangeCommentary(Request $request): JsonResponse
    {
        if (empty($request->toArray())) {
            return new JsonResponse(['error' => 'request body is empty'], 422);
        }

        $data = $request->toArray();

        try {
            $result = $this->bookingService->updateBookingComment(
                (int) $data['id'],
                $data['comment'] ?? ''
            );

            if ($result) {
                return new JsonResponse([
                    'status' => 'OK',
                    'message' => 'Booking comment updated successfully',
                ], 200);
            } else {
                return new JsonResponse([
                    'error' => 'Booking not found or update failed',
                ], 404);
            }
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to update booking comment: '.$e->getMessage(),
            ], 500);
        }
    }

    #[Route('/api/user/bookings', name: 'app_user_bookings', methods: ['GET'])]
    public function getUserBookings(Request $request): JsonResponse
    {
        $phoneNumber = $request->query->get('phone_number', '');

        if (!$phoneNumber) {
            return new JsonResponse([
                'error' => 'Phone number is required',
            ], 400);
        }

        try {
            $bookings = $this->bookingService->getUserBookings($phoneNumber);

            $bookingsArray = array_map(function ($booking) {
                return [
                    'id' => $booking->getId(),
                    'guestName' => $booking->getUser()->getName(),
                    'phoneNumber' => $booking->getUser()->getPhoneNumber(),
                    'houseName' => $booking->getHouse()->getHouseName(),
                    'status' => $booking->getStatus(),
                    'comment' => $booking->getComment(),
                ];
            }, $bookings);

            return new JsonResponse([
                'status' => 'OK',
                'count' => count($bookingsArray),
                'bookings' => $bookingsArray,
            ], 200);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to retrieve user bookings: '.$e->getMessage(),
            ], 500);
        }
    }
}
