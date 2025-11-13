<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\BookingStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<SummerHouse>
 */
class SummerHouseRepository extends ServiceEntityRepository
{
    public function findAvailableHouses(
        array $activeStatus = [BookingStatus::PENDING, BookingStatus::CONFIRMED, BookingStatus::CANCELLED],
    ): array {
        $subQuery = $this->getEntityManager()->createQueryBuilder()
        ->select('IDENTITY(b.house)')
        ->from('App\Entity\Booking', 'b')
        ->where('b.status IN (:activeStatuses)');

        $qb = $this->createQueryBuilder('h');

        return $qb
            ->where($qb->expr()->notIn('h.id', $subQuery->getDQL()))
            ->setParameter('activeStatuses', $activeStatus)
            ->getQuery()
            ->getResult();
    }
}
