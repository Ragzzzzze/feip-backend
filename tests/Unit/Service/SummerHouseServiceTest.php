<?php

namespace App\Tests\Unit\Service;

use App\Dto\SummerHouseDto;
use App\Entity\SummerHouse;
use App\Repository\SummerHouseRepository;
use App\Services\SummerHouseService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class SummerHouseServiceTest extends TestCase
{
    private SummerHouseService $summerHouseService;
    private $summerHouseRepositoryMock;
    private $entityManagerMock;
    private $validatorMock;

    protected function setUp(): void
    {
        $this->summerHouseRepositoryMock = $this->createMock(SummerHouseRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);

        $this->summerHouseService = new SummerHouseService(
            $this->summerHouseRepositoryMock,
            $this->entityManagerMock,
            $this->validatorMock
        );
    }

    public function testCreateHouseSuccess(): void
    {
        $houseDto = new SummerHouseDto(
            name: 'Test Villa',
            price: 150.0,
            sleeps: 4,
            distanceToSea: 100,
            hasTV: true
        );

        $this->validatorMock->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(SummerHouse::class));
            
        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $house = $this->summerHouseService->createHouse($houseDto);

        $this->assertInstanceOf(SummerHouse::class, $house);
        $this->assertEquals('Test Villa', $house->getHouseName());
        $this->assertEquals(150.0, $house->getPrice());
        $this->assertEquals(4, $house->getSleeps());
        $this->assertEquals(100, $house->getDistanceToSea());
        $this->assertEquals('Pool, WiFi', $house->getHasTV());
    }

    public function testGetAllHouses(): void
    {
        $house1 = new SummerHouse();
        $house1->setHouseName('House 1');
        
        $house2 = new SummerHouse();
        $house2->setHouseName('House 2');

        $this->summerHouseRepositoryMock->method('findAll')
            ->willReturn([$house1, $house2]);

        $result = $this->summerHouseService->getAllHouses();

        $this->assertCount(2, $result);
        $this->assertEquals('House 1', $result[0]->getHouseName());
        $this->assertEquals('House 2', $result[1]->getHouseName());
    }

    public function testGetAvailableHouses(): void
    {
        $availableHouse = new SummerHouse();
        $availableHouse->setHouseName('Available House');

        $this->summerHouseRepositoryMock->method('findAvailableHouses')
            ->willReturn([$availableHouse]);

        $result = $this->summerHouseService->getAvailableHouses();

        $this->assertCount(1, $result);
        $this->assertEquals('Available House', $result[0]->getHouseName());
    }
}


