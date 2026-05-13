<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @return Order[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('o')
            ->join('o.user', 'u')
            ->addSelect('u')
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Order[]
     */
    public function findByUserOrderedByDate(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneWithDetails(int $id): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.address', 'a')
            ->addSelect('a')
            ->leftJoin('o.orderLines', 'ol')
            ->addSelect('ol')
            ->leftJoin('ol.product', 'p')
            ->addSelect('p')
            ->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
