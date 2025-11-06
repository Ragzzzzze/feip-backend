<?php

namespace App\Services;

use App\Entity\SummerHouse;
use App\Dto\SummerHouseDto;
use App\Repository\SummerHouseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SummerHouseService 
{
    public function __construct(
        private SummerHouseRepository $summerHouseRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {}

    public function getAllHouses(): array
    {
        return $this->summerHouseRepository->findAll();
    }

    public function getHouse(int $houseId): ?SummerHouse
    {
        return $this->summerHouseRepository->find($houseId);
    }

    public function getAvailableHouses(): array
    {
        return $this->summerHouseRepository->findAvailableHouses();
    }

    public function createHouse(SummerHouseDto $houseDto): SummerHouse
    {
        $errors = $this->validator->validate($houseDto);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException('Invalid house data');
        }

        $house = new SummerHouse();
        $house->setHouseName($houseDto->name);
        $house->setPrice($houseDto->price);
        $house->setSleeps($houseDto->sleeps);
        $house->setDistanceToSea($houseDto->distanceToSea);
        $house->setHasTV($houseDto->hasTV);

        $this->entityManager->persist($house);
        $this->entityManager->flush();

        return $house;
    }
}