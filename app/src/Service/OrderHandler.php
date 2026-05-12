<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class OrderHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private OrderRepository $orderRepository,
        private Security $security
    ) {}

    public function handleCheckout(Address $address, float $total): Order
    {
        $user = $this->security->getUser();

        $existingOrder = $this->orderRepository->findOneBy([
            'user' => $user,
            'status' => Order::STATUS_PENDING_PAYMENT,
        ]);

        if ($existingOrder && $existingOrder->getAddress()) {
            return $existingOrder;
        }

        if ($existingOrder) {
            $order = $existingOrder;
            $order->setTotalAmount($total);
        } else {
            $order = new Order();
            $order->setUser($user);
            $order->setStatus(Order::STATUS_PENDING_PAYMENT);
            $order->setTotalAmount($total);
            $this->em->persist($order);
        }

        $address->setOrderRef($order);
        $this->em->persist($address);
        $this->em->flush();

        return $order;
    }
}