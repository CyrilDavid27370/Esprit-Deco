<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartLine;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class CartHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private RequestStack $requestStack,
        private Security $security,
        private EntityManagerInterface $em,
        private CartRepository $cartRepository
    ) {}

    public function getCart(): array
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $items = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            if (!$product) continue;

            $subtotal = $product->getPrice() * $quantity;
            $total += $subtotal;

            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    public function getTotalQuantity(): int
    {
    $cart = $this->requestStack->getSession()->get('cart', []);
    return array_sum($cart);
    }

    public function getCartFromDb(): ?Cart
    {
        $user = $this->security->getUser();
        if (!$user) return null;

        return $this->cartRepository->findOneBy([
            'user' => $user,
            'status' => Cart::STATUS_OPEN
        ]);
    }

}
