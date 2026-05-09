<?php

namespace App\EventSubscriber;

use App\Entity\Cart;
use App\Entity\CartLine;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class CartSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private CartRepository $cartRepository,
        private ProductRepository $productRepository,
        private EntityManagerInterface $em
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) return;

        $user = $event->getUser();

        // Cherche un panier existant en BDD
        $dbCart = $this->cartRepository->findOneBy([
            'User' => $user,
            'status' => Cart::STATUS_OPEN
        ]);

        // Crée un nouveau panier si l'utilisateur n'en a pas
        if (!$dbCart) {
            $dbCart = new Cart();
            $dbCart->setUser($user);
            $dbCart->setStatus(Cart::STATUS_OPEN);
            $this->em->persist($dbCart);
        }

        // Transfère les produits de la session vers la BDD
        foreach ($cart as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            if (!$product) continue;

            // Cherche si le produit est déjà dans le panier BDD
            $existingLine = null;
            foreach ($dbCart->getCartLines() as $cartLine) {
                if ($cartLine->getProduct() === $product) {
                    $existingLine = $cartLine;
                    break;
                }
            }

            if ($existingLine) {
                $existingLine->setQuantity($existingLine->getQuantity() + $quantity);
            } else {
                $cartLine = new CartLine();
                $cartLine->setCart($dbCart);
                $cartLine->setProduct($product);
                $cartLine->setQuantity($quantity);
                $this->em->persist($cartLine);
            }
        }

        $this->em->flush();
        $session->remove('cart');
    }
}