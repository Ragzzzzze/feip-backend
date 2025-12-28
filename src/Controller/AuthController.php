<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AuthController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/api/auth/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $response = null;

        $phoneNumber = $data['phone_number'] ?? null;
        $password = $data['password'] ?? null;

        if (null === $phoneNumber || null === $password || !isset($password) || !isset($phoneNumber)) {
            $response = new JsonResponse([
                'error' => 'Phone number and password are required',
            ], 400);
        }

        if (null === $response && null !== $phoneNumber && null !== $password) {
            $user = $this->userRepository->findOneBy(['phoneNumber' => $phoneNumber]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
                $response = new JsonResponse([
                    'error' => 'Invalid credentials',
                ], 401);
            } else {
                try {
                    $response = new JsonResponse([
                        'status' => 'OK',
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user->getId(),
                            'name' => $user->getName(),
                            'phone_number' => $user->getPhoneNumber(),
                            'roles' => $user->getRoles(),
                        ],
                    ], 200);
                } catch (Exception $e) {
                    $response = new JsonResponse([
                        'error' => 'Login failed: ' . $e->getMessage(),
                    ], 400);
                }
            }
        }

        return $response ?? new JsonResponse(['error' => 'Unknown error'], 500);
    }

    #[Route('/api/auth/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'OK',
            'message' => 'Logout successful',
        ], 200);
    }

    #[Route('/api/auth/profile', name: 'api_auth_profile', methods: ['GET'])]
    public function profile(#[CurrentUser] ?User $user): JsonResponse
    {
        $_response = null;

        if ($user) {
            $_response = new JsonResponse([
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'phone_number' => $user->getPhoneNumber(),
                    'roles' => $user->getRoles(),
                ],
            ], 200);
        } else {
            $_response = new JsonResponse([
                'error' => 'Not authenticated',
            ], 401);
        }

        return $_response;
    }
}
