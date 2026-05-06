<?php

namespace App\Controller\AdminController;

use App\Entity\Image;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ImageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_ADMIN')]
final class AdminProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $em,
        private ImageHandler $imageHandler,
        private SluggerInterface $slugger)
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

        #[Route('/admin/product/add', name: 'app_admin_product_add')]
    public function add(Request $request): Response
    {
        $form = $this->createForm(ProductType::class);
        $form->handleRequest($request);
        $product = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();

            foreach ($imageFiles as $index => $imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $this->slugger->slug($originalFilename);
                $newFileName = uniqid() . '-' . $safeFileName . '.' . $imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/products',
                    $newFileName
                );

                $image = new Image();
                $image->setPath('uploads/products/' . $newFileName);
                $image->setAlt($originalFilename);
                $image->setIsPrincipal($index === 0);
                $product->addImage($image);
            }

            $this->em->persist($product);
            $this->em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès.');
            return $this->redirectToRoute('app_admin_product_list');
        }

        return $this->render('admin/product_add.html.twig', [
            'form' => $form,
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

