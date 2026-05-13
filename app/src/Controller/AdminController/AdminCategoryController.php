<?php

namespace App\Controller\AdminController;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminCategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/admin/category', name: 'app_admin_category_list')]
    public function category_list(): Response
    {
        $categories = $this->categoryRepository->findAll();

        return $this->render('admin/category_list.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/admin/category/save/{id}', name: 'app_admin_category_save', requirements: ['id' => '\d+'], defaults: ['id' => null])]
    public function save(Request $request, #[MapEntity] ?Category $category = null): Response
    {
        $category = $category ?? new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isNew = $category->getId() === null;
            $this->em->persist($category);
            $this->em->flush();

            $this->addFlash('success', $isNew ? 'Catégorie ajoutée avec succès.' : 'Catégorie modifiée avec succès.');
            return $this->redirectToRoute('app_admin_category_list');
        }

        return $this->render('admin/category_save.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }
}
