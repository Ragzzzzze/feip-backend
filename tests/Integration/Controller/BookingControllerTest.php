<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\Booking;
use App\Entity\SummerHouse;
use App\Entity\User;
use App\Enum\BookingStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BookingControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private User $testUser;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $this->entityManager->createQuery('DELETE FROM App\Entity\Booking')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\SummerHouse')->execute();

        $this->testUser = new User();
        $this->testUser->setName('test');
        $this->testUser->setPhoneNumber('+123456789');
        $this->testUser->setPassword($this->passwordHasher->hashPassword($this->testUser, 'password123'));
        $this->testUser->setRoles(['ROLE_USER']);

        $this->entityManager->persist($this->testUser);
        $this->entityManager->flush();

        $this->client->loginUser($this->testUser);
    }

    public function testCreateBookingSuccess(): void
    {
        $house = new SummerHouse();
        $house->setHouseName('Test Villa');
        $house->setPrice(150);
        $house->setSleeps(4);
        $house->setDistanceToSea(100);
        $house->setHasTV(true);

        $this->entityManager->persist($house);
        $this->entityManager->flush();

        $this->client->request(
            'POST',
            '/api/booking',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phoneNumber' => $this->testUser->getPhoneNumber(),
                'houseId' => $house->getId(),
                'comment' => 'Test booking comment',
            ])
        );

        $response = $this->client->getResponse();
        if (500 === $response->getStatusCode()) {
            echo 'ERROR: ' . $response->getContent() . "\n";
        }
        $this->assertEquals(201, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('OK', $responseData['status']);
        $this->assertEquals('Booking created successfully', $responseData['message']);
        $this->assertArrayHasKey('booking_id', $responseData);
    }

    public function testCreateBookingEmptyRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/booking',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Request body is empty', $responseData['error']);
    }

    public function testCreateBookingInvalidHouse(): void
    {
        $this->client->request(
            'POST',
            '/api/booking',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phoneNumber' => $this->testUser->getPhoneNumber(),
                'houseId' => 999,
                'comment' => 'Test comment',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Summer house not found', $responseData['error']);
    }

    public function testGetUserBookingsSuccess(): void
    {
        $house = new SummerHouse();
        $house->setHouseName('Test House');
        $house->setPrice(100);
        $house->setSleeps(4);
        $house->setDistanceToSea(50);
        $house->setHasTV(true);

        $booking = new Booking();
        $booking->setClient($this->testUser);
        $booking->setHouse($house);
        $booking->setStatus(BookingStatus::PENDING);
        $booking->setComment('Test booking');

        $this->entityManager->persist($this->testUser);
        $this->entityManager->persist($house);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/user/bookings?phone_number=' . $this->testUser->getPhoneNumber());

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('OK', $responseData['status']);
        $this->assertCount(1, $responseData['bookings']);
        $this->assertEquals('Test booking', $responseData['bookings'][0]['comment']);
        $this->assertEquals('Test User', $responseData['bookings'][0]['guestName']);
        $this->assertEquals('+123456789', $responseData['bookings'][0]['phoneNumber']);
    }

    public function testGetUserBookingsNoPhoneNumber(): void
    {
        $this->client->request('GET', '/api/user/bookings');

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Phone number is required', $responseData['error']);
    }

    public function testUpdateBookingCommentSuccess(): void
    {
        $house = new SummerHouse();
        $house->setHouseName('Test House');
        $house->setPrice(100);
        $house->setSleeps(4);
        $house->setDistanceToSea(50);
        $house->setHasTV(true);

        $booking = new Booking();
        $booking->setClient($this->testUser);
        $booking->setHouse($house);
        $booking->setStatus(BookingStatus::PENDING);
        $booking->setComment('Old comment');

        $this->entityManager->persist($this->testUser);
        $this->entityManager->persist($house);
        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        $bookingId = $booking->getId();

        $this->client->request(
            'PATCH',
            '/api/booking',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'id' => $bookingId,
                'comment' => 'Updated comment',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('OK', $responseData['status']);
        $this->assertEquals('Booking comment updated successfully', $responseData['message']);
    }

    private function clearDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM bookings');
        $connection->executeStatement('DELETE FROM houses');
        $connection->executeStatement('DELETE FROM users');
    }
}
