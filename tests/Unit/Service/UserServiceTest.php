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

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private $userRepositoryMock;
    private $entityManagerMock;
    private $validatorMock;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->userService = new UserService(
            $this->userRepositoryMock,
            $this->entityManagerMock,
            $this->validatorMock
        );
    }

    public function testCreateUserSuccess(): void
    {
        $userDto = new UserDto(
            name: 'John Doe',
            phoneNumber: '+123456789'
        );

        $this->validatorMock->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->userRepositoryMock->method('findOneBy')
            ->with(['phoneNumber' => '+123456789'])
            ->willReturn(null);

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(User::class));

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $user = $this->userService->createUser($userDto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals('+123456789', $user->getPhoneNumber());
    }

    public function testCreateUserValidationFailed(): void
    {
        $userDto = new UserDto(
            name: '',
            phoneNumber: 'invalid-phone12312312321212'
        );

        $violationsMock = $this->createMock(ConstraintViolationList::class);
        $violationsMock->method('count')->willReturn(2);

        $this->validatorMock->method('validate')
            ->willReturn($violationsMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Name and phone number are required');

        $this->userService->createUser($userDto);
    }

    public function testCreateUserPhoneAlreadyExists(): void
    {
        $userDto = new UserDto(
            name: 'John Doe',
            phoneNumber: '+123456789'
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

    public function testFindUserByPhoneFound(): void
    {
        $existingUser = new User();
        $existingUser->setPhoneNumber('+123456789');

        $this->userRepositoryMock->method('findOneBy')
            ->with(['phoneNumber' => '+123456789'])
            ->willReturn($existingUser);

        $result = $this->userService->findUserByPhone('+123456789');

        $this->assertSame($existingUser, $result);
    }

    public function testFindUserByPhoneNotFound(): void
    {
        $this->userRepositoryMock->method('findOneBy')
            ->with(['phoneNumber' => '+000000000'])
            ->willReturn(null);
        $result = $this->userService->findUserByPhone('+000000000');

        $this->assertNull($result);
    }
}
