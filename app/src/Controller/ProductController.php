<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAllWithCategoryAndImages();

        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show',requirements: ['id' => '\d+'])]
    public function show(Product $product):Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
