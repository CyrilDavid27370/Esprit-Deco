<?php

namespace App\Controller\AdminController;

use App\Entity\User;
use App\Form\UserRoleType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminUserController extends AbstractController
{
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/admin/user', name: 'app_admin_user_list')]
    public function userList(Request $request, PaginatorInterface $paginator): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $queryBuilder = $this->userRepository->createAdminUserListQueryBuilder();
        $users = $paginator->paginate($queryBuilder, $page, self::ITEMS_PER_PAGE);

        return $this->render('admin/user_list.html.twig', [
            'users' => $users,
            'current_user' => $this->getUser(),
        ]);
    }

    #[Route('/admin/user/{id}/role', name: 'app_admin_user_role', requirements: ['id' => '\d+'])]
    public function editRole(Request $request, #[MapEntity] User $user): Response
    {
        if ($user === $this->getUser()) {
            $this->addFlash('warning', 'Vous ne pouvez pas modifier votre propre rôle.');
            return $this->redirectToRoute('app_admin_user_list');
        }

        $currentRole = in_array('ROLE_ADMIN', $user->getRoles()) ? 'ROLE_ADMIN' : 'ROLE_USER';
        $form = $this->createForm(UserRoleType::class, null, []);
        $form->get('role')->setData($currentRole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role = $form->get('role')->getData();
            $user->setRoles($role === 'ROLE_ADMIN' ? ['ROLE_ADMIN'] : []);
            $this->em->flush();

            $this->addFlash('success', 'Rôle modifié avec succès.');
            return $this->redirectToRoute('app_admin_user_list');
        }

        return $this->render('admin/user_role.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/admin/user/{id}/delete', name: 'app_admin_user_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_admin_user_list');
        }

        if (!$this->isCsrfTokenValid('delete-user-' . $id, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_admin_user_list');
        }

        if ($user === $this->getUser()) {
            $this->addFlash('warning', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('app_admin_user_list');
        }

        if (!$user->getOrders()->isEmpty()) {
            $this->addFlash('danger', 'Impossible de supprimer cet utilisateur : il possède des commandes.');
            return $this->redirectToRoute('app_admin_user_list');
        }

        $this->em->remove($user);
        $this->em->flush();
        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

        return $this->redirectToRoute('app_admin_user_list');
    }
}
