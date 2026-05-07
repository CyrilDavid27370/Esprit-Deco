<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private RequestStack $requestStack
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
}
