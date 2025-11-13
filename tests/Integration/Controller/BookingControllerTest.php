<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\Booking;
use App\Entity\SummerHouse;
use App\Entity\User;
use App\Enum\BookingStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookingControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();

        $this->entityManager->createQuery('DELETE FROM App\Entity\Booking')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\SummerHouse')->execute();
    }

    public function testCreateBookingSuccess(): void
    {
        $house = new SummerHouse();
        $house->setHouseName('Test Villa');
        $house->setPrice(150.0);
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
                'phoneNumber' => '+123456789',
                'houseId' => $house->getId(),
                'comment' => 'Test booking comment',
            ])
        );

        $response = $this->client->getResponse();
        if (500 === $response->getStatusCode()) {
            echo 'ERROR: '.$response->getContent()."\n";
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
                'phoneNumber' => '+123456789',
                'houseId' => 999,
                'comment' => 'Test comment',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertStringContainsString('House not found', $responseData['error']);
    }

    public function testGetUserBookingsSuccess(): void
    {
        $user = new User();
        $user->setName('Test User');
        $user->setPhoneNumber('123456789');

        $house = new SummerHouse();
        $house->setHouseName('Test House');
        $house->setPrice(100.0);
        $house->setSleeps(4);
        $house->setDistanceToSea(50);
        $house->setHasTV(true);

        $booking = new Booking();
        $booking->setUser($user);
        $booking->setHouse($house);
        $booking->setStatus(BookingStatus::PENDING);
        $booking->setComment('Test booking');

        $this->entityManager->persist($user);
        $this->entityManager->persist($house);
        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/user/bookings?phone_number=123456789');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('OK', $responseData['status']);
        $this->assertCount(1, $responseData['bookings']);
        $this->assertEquals('Test booking', $responseData['bookings'][0]['comment']);
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
        $user = new User();
        $user->setName('Test User');
        $user->setPhoneNumber('+123456789');

        $house = new SummerHouse();
        $house->setHouseName('Test House');
        $house->setPrice(100.0);
        $house->setSleeps(4);
        $house->setDistanceToSea(50);
        $house->setHasTV(true);

        $booking = new Booking();
        $booking->setUser($user);
        $booking->setHouse($house);
        $booking->setStatus(BookingStatus::PENDING);
        $booking->setComment('Old comment');

        $this->entityManager->persist($user);
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
