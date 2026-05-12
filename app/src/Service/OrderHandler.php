<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderLine;
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
    $isNew = $address->getId() === null;

    $existingOrder = $this->orderRepository->findOneBy([
        'user' => $user,
        'status' => Order::STATUS_PENDING_PAYMENT,
    ]);

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

    if ($isNew) {
        $address->setOrderRef($order);
    }

    $this->em->persist($address);
    $this->em->flush();

    return $order;
}
    public function finalizeOrder(Order $order, array $cartItems): void
    {
        foreach ($cartItems as $item) {
            $orderLine = new OrderLine();
            $orderLine->setMyOrder($order);
            $orderLine->setProduct($item['product']);
            $orderLine->setQuantity($item['quantity']);
            $orderLine->setUnitPrice($item['product']->getPrice());
            $this->em->persist($orderLine);
        }

        $order->setStatus(Order::STATUS_PAID);
        $this->em->flush();
    }
}