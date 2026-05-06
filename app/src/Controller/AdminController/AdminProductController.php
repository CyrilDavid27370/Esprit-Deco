<?php

namespace App\Controller\AdminController;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ImageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $em,
        private ImageHandler $imageHandler)
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

    #[Route('/admin/product/save/{id}', name: 'app_admin_product_save', requirements: ['id' => '\d+'], defaults: ['id' => null])]
    public function save(Request $request, ?int $id = null): Response
    {
        $product = $id ? $this->productRepository->find($id) : new Product();
        $form = $this->createForm(ProductType::class);
        $form->handleRequest($request);
        $product = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->imageHandler->uploadImages($form->get('images')->getData(), $product);
            $this->em->persist($product);
            $this->em->flush();

            $this->addFlash('success', $id ? 'Produit modifié avec succès.' : 'Produit ajouté avec succès.');
            return $this->redirectToRoute('app_admin_product_list');
        }
            return $this->render('admin/product_save.html.twig', [
                'form' => $form,
                'product' => $product,
            ]);
    }

    #[Route('/admin/product/{id}/delete', name: 'app_admin_product_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $product = $this->productRepository->find($id);

        if (!$this->isCsrfTokenValid('delete-product-' . $id, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_admin_product_list');
        }

        $this->imageHandler->deleteFiles($product->getImages());
        $this->em->remove($product);
        $this->em->flush();
        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('app_admin_product_list');
    }
}

