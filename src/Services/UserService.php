<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\UserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserRepository $userRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
    }

    public function createUser(UserDto $userDto): User
    {
        if (empty($userDto->name) || empty($userDto->phoneNumber)) {
            throw new InvalidArgumentException('Name and phone number are required');
        }

        $errors = $this->validator->validate($userDto);
        if (count($errors) > 0) {
            throw new InvalidArgumentException('Invalid user data');
        }

        $existingUser = $this->userRepository->findOneBy(['phoneNumber' => $userDto->phoneNumber]);
        if ($existingUser) {
            throw new InvalidArgumentException('User with this phone already exists');
        }

        $user = new User();
        $user->setName($userDto->name);
        $user->setPhoneNumber($userDto->phoneNumber);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
