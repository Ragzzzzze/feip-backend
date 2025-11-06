<?php

namespace App\Services;

use App\Entity\Booking;
use App\Entity\User;
use App\Entity\SummerHouse;
use App\Dto\BookingDto;
use App\Repository\BookingRepository;
use App\Repository\UserRepository;
use App\Repository\SummerHouseRepository;
use App\Enum\BookingStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookingService 
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private UserRepository $userRepository, 
        private SummerHouseRepository $summerHouseRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {}

    public function createBooking(BookingDto $bookingDto): Booking
    {

        $errors = $this->validator->validate($bookingDto);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException('Invalid booking data');
        }

        $house = $this->summerHouseRepository->find($bookingDto->houseId);

        if (!$house) {
            throw new \InvalidArgumentException('House not found');
        }

        $user = $this->userRepository->findOneBy(['phoneNumber' => $bookingDto->phoneNumber]);

        if (!$user) {
            $user = new User();
            $user->setPhoneNumber($bookingDto->phoneNumber);
            $user->setName(''); // или можно передать имя если есть в DTO
            $this->entityManager->persist($user);
            // НЕ делаем flush здесь - сделаем один flush в конце
        }

        $booking = new Booking();
        $booking->setUser($user);
        $booking->setHouse($house);
        $booking->setComment($bookingDto->comment);
        $booking->setStatus(BookingStatus::PENDING);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return $booking;
    }

    public function updateBookingComment(int $bookingId, string $newComment): bool
    {
        $booking = $this->bookingRepository->find($bookingId);
        
        if (!$booking) { return false; }

        $booking->setComment($newComment);

        $this->entityManager->flush();

        return true;
    }

    public function getUserBookings(string $phoneNumber): array
    {
        $bookings = $this->bookingRepository->findByNumber($phoneNumber);
        return $bookings;
    }
}