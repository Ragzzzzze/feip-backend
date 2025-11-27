<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\UserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private $entityManagerMock;
    private $validatorMock;
    private $userRepositoryMock;
    private $passwordHasherMock;

    protected function setUp(): void
    {   
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->passwordHasherMock = $this->createMock(UserPasswordHasherInterface::class);

        $this->userService = new UserService(
            $this->entityManagerMock,
            $this->validatorMock,
            $this->userRepositoryMock,
            $this->passwordHasherMock,
        );
    }

    public function testCreateUserSuccess(): void
    {
        $userDto = new UserDto(
            name: 'John Doe',
            phoneNumber: '+123456789',
            password: 'password123',
            roles: ['ROLE_USER']
        );

        $this->validatorMock->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->userRepositoryMock->method('findOneBy')
            ->with(['phoneNumber' => '+123456789'])
            ->willReturn(null);

        $this->passwordHasherMock->expects($this->once())
            ->method('hashPassword')
            ->with(
                $this->isInstanceOf(User::class),
                'password123'
            )
            ->willReturn('hashed_password_123');

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (User $user) {
                return $user->getName() === 'John Doe' 
                    && $user->getPhoneNumber() === '+123456789'
                    && $user->getPassword() === 'hashed_password_123'
                    && $user->getRoles() === ['ROLE_USER'];
        }));

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $user = $this->userService->createUser($userDto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals('+123456789', $user->getPhoneNumber());
        $this->assertEquals('hashed_password_123', $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testCreateUserValidationFailed(): void
    {
        $userDto = new UserDto(
            name: '1',
            phoneNumber: 'invalid-phone12312312321212',
            password: 'password456',
            roles: ['ROLE_USER']
        );

        $violationsMock = $this->createMock(ConstraintViolationList::class);
        $violationsMock->method('count')->willReturn(2);

        $this->validatorMock->method('validate')
            ->willReturn($violationsMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid user data');

        $this->userService->createUser($userDto);
    }

    public function testCreateUserPhoneAlreadyExists(): void
    {
        $userDto = new UserDto(
            name: 'John Doe',
            phoneNumber: '+123456789',
            password: 'password123',
            roles: ['ROLE_USER']
        );

        $existingUser = new User();
        $existingUser->setPhoneNumber('+123456789');

        $this->validatorMock->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->userRepositoryMock->method('findOneBy')
            ->with(['phoneNumber' => '+123456789'])
            ->willReturn($existingUser);

        $this->entityManagerMock->expects($this->never())
            ->method('persist');

        $this->entityManagerMock->expects($this->never())
            ->method('flush');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User with this phone already exists');

        $this->userService->createUser($userDto);
    }
}
