<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\UserDto;
use App\Services\UserService;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/api/users/', name: 'api_users_create', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = $request->toArray();

        if (empty($data)) {
            return new JsonResponse(['error' => 'Request body is empty'], 422);
        }

        if (!isset($data['name']) || !isset($data['phone_number']) || !isset($data['password']) || !isset($data['roles'])) {
            return new JsonResponse([
                'error' => 'Missing required fields',
            ], 400);
        }

        try {
            $userDto = new UserDto(
                name: $data['name'],
                phoneNumber: $data['phone_number'],
                password: $data['password'],
                roles: $data['roles'] ?? ['ROLE_USER'],
            );

            $user = $this->userService->createUser($userDto);

            return new JsonResponse([
                'status' => 'OK',
                'message' => 'User created successfully',
                'user_id' => $user->getId(),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 400);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to create user: ' . $e->getMessage(),
            ], 500);
        }
    }
}
