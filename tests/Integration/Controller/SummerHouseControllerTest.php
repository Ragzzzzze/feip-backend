<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\SummerHouse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SummerHouseControllerTest extends WebTestCase
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

    public function testGetAllHousesSuccess(): void
    {
        $house1 = new SummerHouse();
        $house1->setHouseName('Villa 1');
        $house1->setPrice(100.0);
        $house1->setSleeps(4);
        $house1->setDistanceToSea(50);
        $house1->setHasTV(true);

        $house2 = new SummerHouse();
        $house2->setHouseName('Villa 2');
        $house2->setPrice(150.0);
        $house2->setSleeps(6);
        $house2->setDistanceToSea(100);
        $house2->setHasTV(false);

        $this->entityManager->persist($house1);
        $this->entityManager->persist($house2);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/houses');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(2, $responseData);
        $this->assertEquals('Villa 1', $responseData[0]['name']);
        $this->assertEquals('Villa 2', $responseData[1]['name']);
    }

    public function testGetAvailableHousesSuccess(): void
    {
        $house = new SummerHouse();
        $house->setHouseName('Available Villa');
        $house->setPrice(120.0);
        $house->setSleeps(4);
        $house->setDistanceToSea(75);
        $house->setHasTV(true);

        $this->entityManager->persist($house);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/available-houses');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertCount(1, $responseData);
        $this->assertEquals('Available Villa', $responseData[0]['name']);
    }

    public function testCreateHouseSuccess(): void
    {
        $this->client->request(
            'POST',
            '/api/houses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'New Villa',
                'price' => 200.0,
                'sleeps' => 6,
                'distanceToSea' => 150,
                'hasTV' => true,
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('OK', $responseData['status']);
        $this->assertEquals('House created successfully', $responseData['message']);
        $this->assertArrayHasKey('house_id', $responseData);

        $house = $this->entityManager->getRepository(SummerHouse::class)->find($responseData['house_id']);
        $this->assertNotNull($house);
        $this->assertEquals('New Villa', $house->getHouseName());
    }

    public function testCreateHouseEmptyRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/houses',
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

    private function clearDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM bookings');
        $connection->executeStatement('DELETE FROM houses');
        $connection->executeStatement('DELETE FROM users');
    }
}
