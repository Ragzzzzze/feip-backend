<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\SummerHouseDto;
use App\Entity\SummerHouse;
use App\Repository\SummerHouseRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SummerHouseService
{
    public function __construct(
        private SummerHouseRepository $summerHouseRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    public function getAllHouses(): array
    {
        $result = $this->summerHouseRepository->findAll();
        $data = [];

        foreach ($result as $house) {
            $data[] = [
                'id' => $house->getId(),
                'name' => $house->getHouseName(),
                'price' => $house->getPrice(),
                'sleeps' => $house->getSleeps(),
                'distance_to_sea' => $house->getDistanceToSea(),
                'has_TV' => $house->getHasTV(),
            ];
        }

        return $data;
    }

    public function getHouse(int $houseId): ?SummerHouse
    {
        $result = $this->summerHouseRepository->find($houseId);
        if (null === $result) {
            return null;
        }

        return $result;
    }

    public function getAvailableHouses(): array
    {
        $result = $this->summerHouseRepository->findAvailableHouses();

        return $result;
    }

    public function createHouse(SummerHouseDto $houseDto): SummerHouse
    {
        $errors = $this->validator->validate($houseDto);
        if (count($errors) > 0) {
            throw new InvalidArgumentException('Invalid house data');
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
