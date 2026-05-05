<?php

namespace App\Controller\AdminController;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminProductController extends AbstractController
{
    public function __construct(private ProductRepository $productRepository, private EntityManagerInterface $em)
    {
    }

    #[Route('/admin/product', name: 'app_admin_product_list')]
    public function product_list(): Response
    {
        $products = $this->productRepository->findAllWithCategory();


        return $this->render('admin/product_list.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('admin/product/delete/{id}', name: 'app_admin_product_delete')]
    public function delete(int $id, Request $request): Response
    {
        $product = $this->productRepository->find($id);

        if ($this->isCsrfTokenValid('delete-product-' . $id, $request->request->get('_token'))) {
            foreach ($product->getImages() as $image) {
                $imagePath = $this->getParameter('kernel.project_dir') . '/public/' . $image->getPath();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $this->em->remove($product);
            $this->em->flush();
            $this->addFlash('success', 'produit supprimé avec succès');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide');
        }
        return $this->redirectToRoute('app_admin_product_list');
    }
}
