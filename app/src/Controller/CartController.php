<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart/add/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function add(Product $product, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);

        $productId = $product->getId();
        $cart[$productId] = ($cart[$product->getId()] ?? 0) + 1;

        $session->set('cart', $cart);

        $this->addFlash('success', $product->getTitle() . ' ' . ' a été ajouté au panier');


        return $this->redirectToRoute("app_home");
    }
}
