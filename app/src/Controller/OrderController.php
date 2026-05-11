<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Form\AddressType;
use App\Service\CartHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class OrderController extends AbstractController
{
    public function __construct(
        private CartHandler $cartHandler,
        private EntityManagerInterface $em
    ) {}

    #[Route('/order/checkout/{id}', name: 'app_order_checkout', defaults: ['id' => null])]
    public function checkout(Request $request, ?Address $address = null): Response
    {
        $newAddress = $address === null;
        $address = $address ?? new Address();

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($newAddress) {
                $existingOrder = $this->em->getRepository(Order::class)->findOneBy([
                    'user' => $this->getUser(),
                    'status' => Order::STATUS_PENDING_PAYMENT,
                ]);

                if ($existingOrder && $existingOrder->getAddress()) {
                    return $this->redirectToRoute('app_order_confirm', ['id' => $existingOrder->getId()]);
                }

                if ($existingOrder) {
                    $order = $existingOrder;
                    $order->setTotalAmount($this->cartHandler->getCart()['total']);
                } else {
                    $order = new Order();
                    $order->setUser($this->getUser());
                    $order->setStatus(Order::STATUS_PENDING_PAYMENT);
                    $order->setTotalAmount($this->cartHandler->getCart()['total']);
                    $this->em->persist($order);
                }

                $address->setOrderRef($order);
            }

            $this->em->persist($address);
            $this->em->flush();

            $order = $address->getOrderRef();
            if (!$order) {
                $this->em->refresh($address);
                $order = $address->getOrderRef();
            }

            return $this->redirectToRoute('app_order_confirm', ['id' => $order->getId()]);
        }

        return $this->render('order/checkout.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/order/confirm/{id}', name: 'app_order_confirm')]
    public function confirm(Order $order): Response
    {
        return $this->render('order/confirm.html.twig', [
            'order' => $order,
        ]);
    }
}