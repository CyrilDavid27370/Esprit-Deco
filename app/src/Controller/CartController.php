<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack
    ) {}

    #[Route('/cart/add/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function add(Product $product, ProductRepository $productRepository): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $productId = $product->getId();
        $cart[$productId] = ($cart[$productId] ?? 0) + 1;
        $session->set('cart', $cart);
        $this->addFlash('success', $product->getTitle() . ' a été ajouté au panier');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/cart/decrease/{id}', name: 'app_cart_decrease', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function decrease(Product $product): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $id = $product->getId();

        if (isset($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $session->set('cart', $cart);
        return $this->redirectToRoute('app_home');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function remove(Product $product): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        unset($cart[$product->getId()]);
        $session->set('cart', $cart);
        return $this->redirectToRoute('app_home');
    }

    #[Route('/cart/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(): Response
    {
        $this->requestStack->getSession()->remove('cart');
        return $this->redirectToRoute('app_home');
    }
}
