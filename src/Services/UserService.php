<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\UserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function createUser(UserDto $userDto): User
    {
        if (empty($userDto->name) || empty($userDto->phoneNumber) || empty($userDto->password) || empty($userDto->roles)) {
            throw new InvalidArgumentException('Fields are required');
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
        $user->setRoles($userDto->roles);

        $plaintextPassword = $userDto->password;
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plaintextPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
