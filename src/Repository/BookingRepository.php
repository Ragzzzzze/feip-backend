<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookingRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Booking::class);
    }

    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.user', 'u')
            ->leftJoin('b.house', 'h')
            ->addSelect('u', 'h')
            ->getQuery()
            ->getResult();
    }

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