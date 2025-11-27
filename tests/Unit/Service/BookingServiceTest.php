<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\BookingDto;
use App\Entity\Booking;
use App\Entity\SummerHouse;
use App\Entity\User;
use App\Enum\BookingStatus;
use App\Repository\BookingRepository;
use App\Repository\SummerHouseRepository;
use App\Repository\UserRepository;
use App\Services\BookingService;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookingServiceTest extends TestCase
{
    private BookingService $bookingService;
    private $entityManagerMock;
    private $validatorMock;
    private $bookingRepositoryMock;
    private $houseRepositoryMock;
    private $userRepositoryMock;

    protected function setUp(): void
    {   
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->bookingRepositoryMock = $this->createMock(BookingRepository::class);
        $this->houseRepositoryMock = $this->createMock(SummerHouseRepository::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);

        $this->bookingService = new BookingService(
            $this->entityManagerMock,
            $this->validatorMock,
            $this->bookingRepositoryMock,
            $this->houseRepositoryMock,
            $this->userRepositoryMock,
        );
    }

    public function testCreateBookingSuccess(): void
    {
        $bookingDto = new BookingDto(
            phoneNumber: '+123456789',
            houseId: 1,
            comment: 'Test comment'
        );

        $house = new SummerHouse();
        $house->setHouseName('Test House');

        $user = new User();
        $user->setPhoneNumber('+123456789');

        $this->validatorMock->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->houseRepositoryMock->method('find')
            ->with(1)
            ->willReturn($house);

        $this->userRepositoryMock->method('findOneBy')
            ->with(['phoneNumber' => '+123456789'])
            ->willReturn($user);

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Booking::class));

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $booking = $this->bookingService->createBooking($bookingDto);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals(BookingStatus::PENDING, $booking->getStatus());
        $this->assertEquals('Test comment', $booking->getComment());
        $this->assertEquals($user, $booking->getClient());
        $this->assertEquals($house, $booking->getHouse());
    }

    public function testCreateBookingHouseNotFound(): void
    {
        $bookingDto = new BookingDto('+123456789', 999, 'Test');

        $this->validatorMock->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->houseRepositoryMock->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Summer house not found');

        $this->bookingService->createBooking($bookingDto);
    }

    public function testUpdateBookingCommentSuccess(): void
    {
        $booking = new Booking();
        $booking->setComment('Old comment');

        $this->bookingRepositoryMock->method('find')
            ->with(1)
            ->willReturn($booking);

        $result = $this->bookingService->updateBookingComment(1, 'New comment');

        $this->assertTrue($result);
        $this->assertEquals('New comment', $booking->getComment());
    }

    public function testUpdateBookingCommentNotFound(): void
    {
        $this->bookingRepositoryMock->method('find')
            ->with(999)
            ->willReturn(null);

        $result = $this->bookingService->updateBookingComment(999, 'New comment');

        $this->assertFalse($result);
    }

    public function testGetUserBookingsFound(): void
    {
        $user = new User();
        $user->setPhoneNumber('+123456789');

        $booking = new Booking();
        $booking->setClient($user);

        $this->userRepositoryMock->method('findOneBy')
            ->with(['phoneNumber' => '+123456789'])
            ->willReturn($user);

        $this->bookingRepositoryMock->method('findByNumber')
            ->with($user->getPhoneNumber())
            ->willReturn([$booking]);

        $result = $this->bookingService->getUserBookings('+123456789');

        $this->assertCount(1, $result);
        $this->assertSame($booking, $result[0]);
    }

    public function testGetUserBookingsUserNotFound(): void
    {
        $this->userRepositoryMock->method('findOneBy')
            ->with(['phoneNumber' => '+000000000'])
            ->willReturn(null);

        $result = $this->bookingService->getUserBookings('+000000000');

        $this->assertCount(0, $result);
    }
}
