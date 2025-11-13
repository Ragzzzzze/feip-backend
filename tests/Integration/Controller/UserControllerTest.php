<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
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

    public function testCreateUserSuccess(): void
    {
        $this->client->request(
            'POST',
            '/api/users/',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'John Doe',
                'phone_number' => '123456789',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('OK', $responseData['status']);
        $this->assertEquals('User created successfully', $responseData['message']);
        $this->assertArrayHasKey('user_id', $responseData);
    }

    public function testCreateUserEmptyRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/users/',
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
