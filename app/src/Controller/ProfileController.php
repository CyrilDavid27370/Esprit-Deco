<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $orders = $this->orderRepository->findByUserOrderedByDate($user);

        return $this->render('profile/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/profile/order/{id}', name: 'app_profile_order', requirements: ['id' => '\d+'])]
    public function order(int $id): Response
    {
        $order = $this->orderRepository->findOneWithDetails($id);

        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($order->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette commande.');
        }

        return $this->render('profile/order.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/profile/password', name: 'app_profile_password')]
    public function changePassword(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(
                    new FormError('Mot de passe actuel incorrect.')
                );
            } elseif ($newPassword !== $confirmPassword) {
                $form->get('confirmPassword')->addError(
                    new FormError('Les deux mots de passe ne correspondent pas.')
                );
            } else {
                $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
                $this->em->flush();

                $this->addFlash('success', 'Mot de passe modifié avec succès. Veuillez vous reconnecter.');
                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('profile/password.html.twig', [
            'form' => $form,
        ]);
    }
}
