<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Form\AddressType;
use App\Service\CartHandler;
use App\Service\OrderHandler;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class OrderController extends AbstractController
{
    public function __construct(
        private CartHandler $cartHandler,
        private EntityManagerInterface $em,
        private OrderHandler $orderHandler,
        #[Autowire('%env(STRIPE_SECRET_KEY)%')]
        private string $stripeSecretKey,
    ) {}

    #[Route('/order/checkout/{id}', name: 'app_order_checkout', defaults: ['id' => null])]
    public function checkout(Request $request, ?Address $address = null): Response
    {
        $newAddress = $address === null;
        $address = $address ?? new Address();

        $form = $this->createForm(AddressType::class);
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

    #[Route('/order/pay/{id}', name: 'app_order_pay', methods: ['POST'])]
    public function pay(Order $order, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('order_pay_' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        Stripe::setApiKey($this->stripeSecretKey);

        $cart = $this->cartHandler->getCart();
        $lineItems = [];

        foreach ($cart['items'] as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['product']->getTitle(),
                    ],
                    'unit_amount' => (int) round($item['product']->getPrice() * 100),
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $successUrl = $this->generateUrl('app_order_success', [], UrlGeneratorInterface::ABSOLUTE_URL)
            . '?session_id={CHECKOUT_SESSION_ID}';

        $cancelUrl = $this->generateUrl(
            'app_order_cancel',
            ['id' => $order->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $stripeSession = StripeSession::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => ['order_id' => $order->getId()],
        ]);

        return $this->redirect($stripeSession->url);
    }

    #[Route('/order/success', name: 'app_order_success')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('app_home');
        }

        Stripe::setApiKey($this->stripeSecretKey);
        $stripeSession = StripeSession::retrieve($sessionId);

        if ($stripeSession->payment_status !== 'paid') {
            $this->addFlash('danger', 'Le paiement n\'a pas été confirmé.');
            return $this->redirectToRoute('app_home');
        }

        $orderId = $stripeSession->metadata->order_id;
        $order = $this->em->getRepository(Order::class)->find($orderId);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        if ($order->getStatus() === Order::STATUS_PENDING_PAYMENT) {
            $this->orderHandler->finalizeOrder($order, $this->cartHandler->getCart()['items']);
            $this->cartHandler->convertCart();
        }

        return $this->render('order/success.html.twig', ['order' => $order]);
    }

    #[Route('/order/cancel/{id}', name: 'app_order_cancel')]
    public function cancel(Order $order): Response
    {
        return $this->redirectToRoute('app_order_confirm', ['id' => $order->getId()]);
    }
}
