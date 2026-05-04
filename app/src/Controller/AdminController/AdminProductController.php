<?php

namespace App\Controller\AdminProduct;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminProductController extends AbstractController
{
    #[Route('/admin/product', name: 'app_admin_product_list')]
    public function product_list(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAllWithCategory();


        return $this->render('admin/product_list.html.twig', [
            'products' => $products,
        ]);
    }
}
