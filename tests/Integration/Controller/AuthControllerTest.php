<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private $testUser;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $this->clearDatabase();

        $this->testUser = new User();
        $this->testUser->setName('Test User');
        $this->testUser->setPhoneNumber('+1234567890');
        $this->testUser->setPassword($this->passwordHasher->hashPassword($this->testUser, 'password123'));
        $this->testUser->setRoles(['ROLE_USER']);

        $this->entityManager->persist($this->testUser);
        $this->entityManager->flush();

        $this->client->loginUser($this->testUser);
    }

    public function testLoginSuccess(): void
    {
        $user = new User();
        $user->setName('John Doe');
        $user->setPhoneNumber('+111111111');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone_number' => '+111111111',
                'password' => 'password123',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('OK', $responseData['status']);
        $this->assertEquals('Login successful', $responseData['message']);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertEquals('John Doe', $responseData['user']['name']);
        $this->assertEquals('+111111111', $responseData['user']['phone_number']);
        $this->assertEquals(['ROLE_USER'], $responseData['user']['roles']);
    }

    public function testLoginUserNotFound(): void
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone_number' => '+000000000',
                'password' => 'password123',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Invalid credentials', $responseData['error']);
    }

    public function testLoginInvalidPassword(): void
    {
        $user = new User();
        $user->setName('John Doe');
        $user->setPhoneNumber('+123456789');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'correct_password'));
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone_number' => '+123456789',
                'password' => 'wrong_password',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Invalid credentials', $responseData['error']);
    }

    public function testLoginMissingCredentials(): void
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'password' => 'password123',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Phone number and password are required', $responseData['error']);

        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone_number' => '+123456789',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Phone number and password are required', $responseData['error']);
    }

    public function testLogoutSuccess(): void
    {
        $this->client->request('POST', '/api/auth/logout');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('OK', $responseData['status']);
        $this->assertEquals('Logout successful', $responseData['message']);
    }

    public function testProfileAuthenticated(): void
    {
        $user = new User();
        $user->setName('John Doe');
        $user->setPhoneNumber('+123456789');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        $this->client->request('GET', '/api/auth/profile');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertEquals('John Doe', $responseData['user']['name']);
        $this->assertEquals('+123456789', $responseData['user']['phone_number']);
        $this->assertEquals(['ROLE_USER'], $responseData['user']['roles']);
    }

    public function testProfileUnauthenticated(): void
    {
        $this->client->request('GET', '/api/auth/profile');

        $response = $this->client->getResponse();
        $this->assertEquals(500, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Not authenticated', $responseData['error']);
    }

    public function testLoginEmptyRequest(): void
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Phone number and password are required', $responseData['error']);
    }

    private function clearDatabase(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Booking')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\SummerHouse')->execute();
    }
}