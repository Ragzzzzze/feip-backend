<?php

namespace App\Tests\Unit\Service;

use App\Dto\BookingDto;
use App\Entity\Booking;
use App\Entity\User;
use App\Entity\SummerHouse;
use App\Repository\BookingRepository;
use App\Repository\UserRepository;
use App\Repository\SummerHouseRepository;
use App\Services\BookingService;
use App\Enum\BookingStatus;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class BookingServiceTest extends TestCase
{
    private BookingService $bookingService;
    private $bookingRepositoryMock;
    private $userRepositoryMock;
    private $houseRepositoryMock;
    private $entityManagerMock;
    private $validatorMock;

    protected function setUp(): void
    {
        $this->bookingRepositoryMock = $this->createMock(BookingRepository::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->houseRepositoryMock = $this->createMock(SummerHouseRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->bookingService = new BookingService(
            $this->bookingRepositoryMock,
            $this->userRepositoryMock,
            $this->houseRepositoryMock,
            $this->entityManagerMock,
            $this->validatorMock
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
        $house->setId(1);
        
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
        $this->assertEquals($user, $booking->getUser());
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('House not found');

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
        $booking->setUser($user);

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