<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class BookingRepository extends ServiceEntityRepository
{
    public function findByNumber(string $phoneNumber): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.client', 'u')
            ->addSelect('u')
            ->where('u.phoneNumber = :phoneNumber')
            ->setParameter('phoneNumber', $phoneNumber)
            ->getQuery()
            ->getResult();
    }
}
