<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Form\AddressType;
use App\Service\CartHandler;
use App\Service\OrderHandler;
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
        private EntityManagerInterface $em,
        private OrderHandler $orderHandler
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
                $address = $form->getData();
                $order = $this->orderHandler->handleCheckout($address, $this->cartHandler->getCart()['total']);
            } else {
                $this->em->persist($address);
                $this->em->flush();
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