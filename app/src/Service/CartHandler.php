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
        return $this->security->getUser()
        ? $this->getCartFromDbFormatted()
        : $this->getCartFromSession();
    }

    private function getCartFromDbFormatted(): array 
    {
        $cart = $this->getCartFromDb();
        if (!$cart) return ['items' => [], 'total' => 0];

        $items = [];
        $total = 0;

        foreach ($cart->getCartLines() as $cartLine) {
            $product = $cartLine->getProduct();
            $quantity = $cartLine->getQuantity();
            $subtotal = $product->getPrice() * $quantity;
            $total += $subtotal;
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
        }
            return ['items' => $items, 'total' => $total];
    }

    private function getCartFromSession(): array 
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
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
                'subtotal' => $subtotal
            ];
        }

        return ['items' => $items, 'total' => $total];
    }

    public function getTotalQuantity(): int
    {
    $user = $this->security->getUser();

    if ($user) {
        $cart = $this->getCartFromDb();
        if (!$cart) return 0;
        $total = 0;
        foreach ($cart->getCartLines() as $cartLine) {
            $total += $cartLine->getQuantity();
        }
        return $total;
    }

    return array_sum($this->requestStack->getSession()->get('cart', []));
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
