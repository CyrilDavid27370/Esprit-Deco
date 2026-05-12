<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartLine;
use App\Entity\Product;
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
            'User' => $user,
            'status' => Cart::STATUS_OPEN
        ]);
    }
    
    public function addToDb(Product $product): void 
    {
        $user = $this->security->getUser();
        $cart = $this->getCartFromDb();

        // Crée un nouveau panier si l'utilisateur n'en a pas
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setStatus(Cart::STATUS_OPEN);
            $this->em->persist($cart);
        }

        // Cherche si le produit est déjà dans le panier
        foreach ($cart->getCartLines() as $cartLine) {
            if ($cartLine->getProduct() === $product) {
                $cartLine->setQuantity($cartLine->getQuantity() + 1);
                $this->em->flush();
                return;
            }
        }

        // Sinon crée une nouvelle ligne
        $cartLine = new CartLine();
        $cartLine->setCart($cart);
        $cartLine->setProduct($product);
        $cartLine->setQuantity(1);
        $this->em->persist($cartLine);
        $this->em->flush();
    }

    public function getProductQuantity(Product $product): int 
    {
        $cart = $this->getCartFromDb();
        if (!$cart) return 0;

        foreach ($cart->getCartLines() as $cartLine) {
            if ($cartLine->getProduct() === $product) {
                return $cartLine->getQuantity();
            }
        }

        return 0;
    }

    public function decreaseInDb(Product $product): int 
    {
        $cart = $this->getCartFromDb();
        if (!$cart) return 0;

        foreach ($cart->getCartLines() as $cartLine) {
            if ($cartLine->getProduct() === $product) {
                if ($cartLine->getQuantity() > 1) {
                    $cartLine->setQuantity($cartLine->getQuantity() - 1);
                } else {
                    $this->em->remove($cartLine);
                }

                $this->em->flush();
                return $cartLine->getQuantity();
            }
        }

        return 0;
    }

    public function removeFromDb(Product $product): void 
    {
        $cart = $this->getCartFromDb();
        if (!$cart) return;

        foreach ($cart->getCartLines() as $cartLine) {
            if ($cartLine->getProduct() === $product) {
                $this->em->remove($cartLine);
                return;
            }
        }
    }

    public function clearDb(): void
    {
        $cart = $this->getCartFromDb();
        if (!$cart) return;

        foreach ($cart->getCartLines() as $cartLine) {
            $this->em->remove($cartLine);
        }

        $this->em->flush();
    }

    public function convertCart(): void
    {
        $cart = $this->getCartFromDb();
        if (!$cart) return;

        foreach ($cart->getCartLines() as $cartLine) {
            $this->em->remove($cartLine);
        }

        $cart->setStatus(Cart::STATUS_CONVERTED);
        $this->em->flush();
    }

}
