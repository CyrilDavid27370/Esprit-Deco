<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    private const PRODUCTS_PER_PAGE = 9;

    #[Route('/', name: 'app_home')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository, PaginatorInterface $paginator): Response
    {
        $categoryId = $request->query->getInt('category') ?: null;

        $minPriceRaw = $request->query->get('minPrice');
        $maxPriceRaw = $request->query->get('maxPrice');
        $minPrice = ($minPriceRaw !== null && $minPriceRaw !== '') ? (float) $minPriceRaw : null;
        $maxPrice = ($maxPriceRaw !== null && $maxPriceRaw !== '') ? (float) $maxPriceRaw : null;

        $searchRaw = $request->query->get('search');
        $search = ($searchRaw !== null && $searchRaw !== '') ? trim($searchRaw) : null;

        $page = max(1, $request->query->getInt('page', 1));

        $queryBuilder = $productRepository->findWithFilters($categoryId, $minPrice, $maxPrice, $search);

        $products = $paginator->paginate($queryBuilder, $page, self::PRODUCTS_PER_PAGE);

        return $this->render('product/index.html.twig', [
            'products'         => $products,
            'categories'       => $categoryRepository->findAll(),
            'selectedCategory' => $categoryId,
            'minPrice'         => $minPrice,
            'maxPrice'         => $maxPrice,
            'search'           => $search,
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
