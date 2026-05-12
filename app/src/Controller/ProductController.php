<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $categoryId = $request->query->getInt('category') ?: null;

        $products = $categoryId
            ? $productRepository->findByCategoryWithPrincipalImage($categoryId)
            : $productRepository->findAllWithCategoryAndPrincipalImage();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categoryRepository->findAll(),
            'selectedCategory' => $categoryId,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show', requirements: ['id' => '\d+'])]
    public function show(int $id, Request $request, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findOneWithCategoryAndAllImages($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'referer' => $request->headers->get('referer'),
        ]);
    }
}
