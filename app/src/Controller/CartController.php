<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CartHandler;
use Symfony\Bundle\SecurityBundle\Security;

final class CartController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private CartHandler $cartHandler,
        private Security $security,
    ) {}

    #[Route('/cart/add/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function add(Product $product, Request $request): Response
    {   
        $user = $this->security->getUser();

        if ($user) {
            $this->cartHandler->addToDb($product);
            $totalQuantity = $this->cartHandler->getTotalQuantity();
            $quantity = $this->cartHandler->getProductQuantity($product);
        } else {
            $session = $this->requestStack->getSession();
            $cart = $session->get('cart', []);
            $productId = $product->getId();
            $cart[$productId] = ($cart[$productId] ?? 0) + 1;
            $session->set('cart', $cart);
            $totalQuantity = array_sum($cart);
            $quantity = $cart[$productId];
        }
        
        // Si requête AJAX → retourne JSON
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'quantity' => $quantity,
                'totalQuantity' => $totalQuantity,
            ]);
        }

        // Sinon → redirection classique
        $this->addFlash('success', $product->getTitle() . ' a été ajouté au panier');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/cart/decrease/{id}', name: 'app_cart_decrease', requirements: ['id' => '\d+'], methods: ['POST'])]
public function decrease(Product $product): Response
{   
    $user = $this->security->getUser();

    if ($user) {
        $quantity = $this->cartHandler->decreaseInDb($product);
        $totalQuantity = $this->cartHandler->getTotalQuantity();
    } else {
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
        $quantity = $cart[$id] ?? 0;
        $totalQuantity = array_sum($cart);
    }

    return $this->json([
        'success' => true,
        'quantity' => $quantity,
        'totalQuantity' => $totalQuantity,
    ]);
}

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', requirements: ['id' => '\d+'], methods: ['POST'])]
public function remove(Product $product): Response
{
    $user = $this->security->getUser();

    if ($user) {
        $this->cartHandler->removeFromDb($product);
        $totalQuantity = $this->cartHandler->getTotalQuantity();
    } else {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        unset($cart[$product->getId()]);
        $session->set('cart', $cart);
        $totalQuantity = array_sum($cart);
    }

    return $this->json([
        'success' => true,
        'quantity' => 0,
        'totalQuantity' => $totalQuantity,
    ]);
}

#[Route('/cart/clear', name: 'app_cart_clear', methods: ['POST'])]
public function clear(): Response
{
    $user = $this->security->getUser();

    if ($user) {
        $this->cartHandler->clearDb();
    } else {
        $this->requestStack->getSession()->remove('cart');
    }

    return $this->json([
        'success' => true,
        'totalQuantity' => 0,
    ]);
}
}
        