<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
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
}
