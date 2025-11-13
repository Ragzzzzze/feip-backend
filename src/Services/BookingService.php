<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\BookingDto;
use App\Entity\Booking;
use App\Entity\User;
use App\Enum\BookingStatus;
use App\Repository\BookingRepository;
use App\Repository\SummerHouseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookingService
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private BookingRepository $bookingRepository;
    private SummerHouseRepository $summerHouseRepository;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        BookingRepository $bookingRepository,
        SummerHouseRepository $summerHouseRepository,
        UserRepository $userRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->bookingRepository = $bookingRepository;
        $this->summerHouseRepository = $summerHouseRepository;
        $this->userRepository = $userRepository;
    }

    public function createBooking(BookingDto $bookingDto): Booking
    {
        $errors = $this->validator->validate($bookingDto);
        if (count($errors) > 0) {
            throw new InvalidArgumentException('Invalid booking data');
        }
        /** @var SummerHouse|null $house */
        $house = $this->summerHouseRepository->find($bookingDto->houseId);

        if (null === $house) {
            throw new InvalidArgumentException('Summer house not found');
        }

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['phoneNumber' => $bookingDto->phoneNumber]);

        if (null === $user) {
            throw new InvalidArgumentException('User not found');
        }

        if (null === $user) {
            $user = new User();
            $user->setPhoneNumber($bookingDto->phoneNumber);
            $user->setName('');
            $this->entityManager->persist($user);
        }

        $booking = new Booking();
        $booking->setUser($user);
        $booking->setHouse($house);
        $booking->setComment($bookingDto->comment ?? '');
        $booking->setStatus(BookingStatus::PENDING);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return $booking;
    }

    public function updateBookingComment(int $bookingId, string $newComment): bool
    {
        $booking = $this->bookingRepository->find($bookingId);

        if (!$booking) {
            return false;
        }

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
