<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\AdressType;
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
    )
    {
    }
    #[Route('/order/checkout', name: 'app_order_checkout')]
    public function checkout(Request $request): Response
    {   
        $form = $this->createForm(AdressType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adress = $form->getData();

            // Créer la commande
            $order = new Order();
            $order->setUser($this->getUser());
            $order->setStatus(Order::STATUS_PENDING_PAYMENT);
            $order->setTotalAmount($this->cartHandler->getCart() ['total']);

            // Associer l'adresse à la commande
            $adress->setOrderRef($order);

            // Persister la commande et l'adresse
            $this->em->persist($order);
            $this->em->persist($adress);
            $this->em->flush();

            return $this->redirectToRoute('app_home');
        }

            return $this->render('order/checkout.html.twig', [
                'form' => $form,
        ]);
    }
}
